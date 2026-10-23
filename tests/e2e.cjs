const assert = require('node:assert/strict');
const fs = require('node:fs');
const {execFileSync} = require('node:child_process');
const {chromium} = require('playwright');
const origin = process.env.TEST_BASE_URL || 'http://localhost:8080';
const marker = 'E2E-PALLET-' + Date.now();
function wp(code) { return execFileSync('docker', ['compose','run','--rm','-T','cli','eval',code], {encoding:'utf8',stdio:['ignore','pipe','pipe']}).trim(); }
const cleanRate = `if (wp_get_environment_type() !== 'local') { WP_CLI::error('Tests require local environment.'); } global $wpdb; foreach ($wpdb->get_col("SELECT option_name FROM {$wpdb->options} WHERE option_name LIKE '_transient_tb_rate_%'") as $n) { delete_transient(substr($n,11)); }`;
const countQuotes = () => Number(wp(`echo count(get_posts(['post_type'=>'tb_quote','post_status'=>'private','numberposts'=>-1,'meta_key'=>'_tb_name','meta_value'=>'${marker}']));`));
(async () => {
  wp(cleanRate);
  const browser = await chromium.launch({executablePath:process.env.CHROMIUM_PATH || '/usr/bin/chromium',headless:true,args:['--no-sandbox']});
  const page = await browser.newPage({viewport:{width:1440,height:1080}});
  page.setDefaultTimeout(15000);
  const errors=[]; page.on('pageerror',error=>errors.push(error.message));
  try {
    const response = await page.goto(origin,{waitUntil:'networkidle'}); assert.equal(response.status(),200);
    assert.equal(await page.locator('.product-card').count(),3);
    assert.equal(await page.locator('.post-card').count(),3);
    await page.locator('img').evaluateAll(images => images.forEach(i => i.loading = 'eager'));
    await page.waitForFunction(() => [...document.images].every(i => i.complete));
    const broken = await page.locator('img').evaluateAll(images => images.filter(i=>!i.complete || i.naturalWidth===0).map(i=>i.src)); assert.deepEqual(broken,[]);
    await page.getByRole('button',{name:'Linh kiện pallet',exact:true}).click();
    assert.equal(await page.locator('.product-card:visible').count(),1);
    assert.match(await page.locator('.product-card:visible h3').innerText(),/Chân cục/);
    await page.getByRole('button',{name:'Tất cả sản phẩm',exact:true}).click();
    await page.locator('.product-card').first().locator('.product-quote').click();
    await page.waitForURL(/product=/); assert.notEqual(await page.locator('#quote-product').inputValue(),'');
    await page.goto(origin+'/san-pham/chan-cuc-pallet/'); assert.match(await page.locator('h1').innerText(),/Chân cục/);
    await page.goto(origin+'/chon-pallet-go-phu-hop/'); assert.match(await page.locator('h1').innerText(),/Chọn pallet/);
    assert.ok(await page.locator('.entry-content h2').count()>=3);
    console.log('PASS homepage, product filter, selected product, product detail and article');
    await page.goto(origin,{waitUntil:'networkidle'});
    for(const width of [1440,768,390,320]) {
      await page.setViewportSize({width,height:900});
      assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),`horizontal overflow at ${width}`);
      if(width<=768) {
        await page.locator('.menu-toggle').click(); assert.equal(await page.locator('.menu-toggle').getAttribute('aria-expanded'),'true');
        await page.keyboard.press('Escape'); assert.equal(await page.locator('.menu-toggle').getAttribute('aria-expanded'),'false');
      }
    }
    console.log('PASS responsive layout at 320, 390, 768 and 1440 px; keyboard menu');
    await page.locator('#quote-name').fill(marker);
    await page.locator('#quote-phone').fill('0900000000');
    await page.locator('#quote-product').selectOption('custom');
    await page.locator('#quote-quantity').fill('120');
    await page.locator('#quote-company').fill('Công ty kiểm thử');
    await page.locator('#quote-message').fill('KIỂM THỬ TỰ ĐỘNG — vui lòng bỏ qua.');
    await page.locator('input[name="consent"]').check();
    const submitted = page.waitForResponse(r=>r.url().includes('admin-post.php') && r.request().method()==='POST');
    await page.locator('.submit-quote').click();
    assert.equal((await submitted).status(),200);
    await page.locator('#quote-feedback.success').waitFor();
    assert.equal(countQuotes(),1);
    const nonce=await page.locator('[name="tb_quote_nonce"]').inputValue();
    const valid={action:'tb_submit_quote',tb_json:'1',tb_quote_nonce:nonce,customer_name:marker,phone:'0900000000',company:'',email:'',product:'custom',quantity:'10',message:'Kiểm thử',consent:'1',website:''};
    async function send(overrides={}) {return page.request.post(origin+'/wp-admin/admin-post.php',{form:{...valid,...overrides}});}
    assert.equal((await send({tb_quote_nonce:'invalid'})).status(),403);
    assert.equal((await send({quantity:'1.5'})).status(),422);
    assert.equal((await send({quantity:'0'})).status(),422);
    assert.equal((await send({phone:'bad'})).status(),422);
    assert.equal((await send({email:'not-an-email'})).status(),422);
    assert.equal((await send({product:'99999999'})).status(),422);
    assert.equal((await send({consent:''})).status(),422);
    assert.equal((await send({website:'spam.example'})).status(),422);
    assert.equal(countQuotes(),1);
    console.log('PASS quote stored; rejects invalid nonce, quantity, phone, email, product, consent and honeypot');
    for(let i=0;i<4;i++) assert.equal((await send()).status(),200);
    assert.equal((await send()).status(),429); assert.equal(countQuotes(),5);
    console.log('PASS rate limit; rejected requests do not create records');
    for(const path of ['/wp-json/wp/v2/tb_quote','/?rest_route=/wp/v2/tb_quote']) {
      const r=await page.request.get(origin+path); assert.equal(r.status(),404);
    }
    const id=Number(wp(`$ids=get_posts(['post_type'=>'tb_quote','post_status'=>'private','numberposts'=>1,'meta_key'=>'_tb_name','meta_value'=>'${marker}','fields'=>'ids']); echo $ids[0];`));
    const privateResponse=await page.request.get(origin+'/?post_type=tb_quote&p='+id); assert.equal(privateResponse.status(),404);
    console.log('PASS anonymous REST and direct URLs cannot read customer requests');
    await page.goto(origin+'/wp-login.php');
    await page.locator('#user_login').fill('siteadmin');
    await page.locator('#user_pass').fill(fs.readFileSync('.runtime/admin-password','utf8').trim());
    await page.locator('#wp-submit').click(); await page.waitForURL(/wp-admin/);
    await page.goto(origin+'/wp-admin/post.php?post='+id+'&action=edit');
    assert.ok((await page.locator('#tb_quote_details').innerText()).includes(marker));
    await page.locator('[name="tb_status"]').selectOption('quoted');
    await page.locator('#publish').click();
    await page.waitForLoadState('networkidle');
    assert.equal(wp(`echo get_post_meta(${id},'_tb_status',true);`),'quoted');
    console.log('PASS admin login, private request details and saved processing status');
    await page.context().clearCookies();
    await page.goto(origin,{waitUntil:'networkidle'});
    await page.setViewportSize({width:1440,height:1080});
    fs.mkdirSync('.runtime/screenshots',{recursive:true});
    await page.screenshot({path:'.runtime/screenshots/desktop.png',fullPage:true});
    await page.setViewportSize({width:390,height:844});
    await page.screenshot({path:'.runtime/screenshots/mobile.png',fullPage:true});
    assert.deepEqual(errors,[]);
    console.log('PASS no browser script errors; desktop and mobile screenshots saved');
  } finally {
    await browser.close();
    wp(`foreach(get_posts(['post_type'=>'tb_quote','post_status'=>'private','numberposts'=>-1,'meta_key'=>'_tb_name','meta_value'=>'${marker}','fields'=>'ids']) as $id) wp_delete_post($id,true);`);
    wp(cleanRate);
  }
})().catch(error=>{console.error(error.message);process.exitCode=1;});
