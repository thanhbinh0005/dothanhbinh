const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {createHash} = require('node:crypto');
const {execFileSync} = require('node:child_process');
const {chromium} = require('playwright');
const origin = process.env.TEST_BASE_URL || 'http://localhost:8082';
const args = ['compose','-p',process.env.TEST_COMPOSE_PROJECT || 'thanh-binh-website-full','-f','compose.yaml','-f','compose.php74.yaml'];
const run = extra => execFileSync('docker',[...args,...extra],{encoding:'utf8',stdio:['ignore','pipe','pipe']}).trim();
const wp = code => run(['run','--rm','-T','cli','eval',code]);
(async()=>{
  assert.equal(wp('echo wp_get_environment_type();'),'local');
  const originalTitle = wp('$p=get_page_by_path("pallet-go-theo-quy-cach",OBJECT,"tb_product");echo $p ? $p->post_title : "";');
  const browser = await chromium.launch({executablePath:process.env.CHROMIUM_PATH || '/usr/bin/chromium',args:['--no-sandbox']});
  const page = await browser.newPage({viewport:{width:1440,height:1050}});
  page.setDefaultTimeout(30000);
  try {
    await page.goto(origin+'/wp-login.php');
    await page.locator('#user_login').fill('siteadmin');
    await page.locator('#user_pass').fill(fs.readFileSync(process.env.TEST_ADMIN_PASSWORD_FILE || '.runtime/website-admin-password','utf8').trim());
    await page.locator('#wp-submit').click(); await page.waitForURL(/wp-admin/);
    await page.goto(origin+'/wp-admin/plugin-install.php?tab=upload');
    await page.locator('#pluginzip').setInputFiles(path.resolve('dist/thanh-binh-website-hoan-chinh-php74.zip'));
    await page.locator('#install-plugin-submit').click(); await page.waitForLoadState('networkidle');
    const replace = page.locator('a[href*="overwrite=update-plugin"]');
    if (await replace.count()) { await replace.click(); await page.waitForLoadState('networkidle'); }
    const activate = page.getByRole('link',{name:'Activate Plugin',exact:true});
    if (await activate.count()) { await activate.click(); await page.waitForLoadState('networkidle'); }
    await page.goto(origin+'/wp-admin/plugins.php');
    const row = page.locator('tr[data-plugin="thanh-binh-orders/thanh-binh-orders.php"]');
    if (await row.locator('.activate a').count()) { await row.locator('.activate a').click(); await page.waitForLoadState('networkidle'); }
    await page.goto(origin+'/wp-admin/admin.php?page=tb-website');
    assert.equal(await page.locator('.tb-website-preview img').evaluate(i=>i.complete&&i.naturalWidth>0),true);
    console.log('PASS complete ZIP installs via Plugins and displays website preview/setup');
    const before = wp('echo json_encode([get_stylesheet(),get_option("tb_content_seed_v1"),wp_count_posts("tb_product")->publish]);');
    const rejected = await page.request.post(origin+'/wp-admin/admin-post.php',{form:{action:'tb_setup_website',tb_setup_nonce:'invalid'}});
    assert.equal(rejected.status(),403);
    assert.equal(wp('echo json_encode([get_stylesheet(),get_option("tb_content_seed_v1"),wp_count_posts("tb_product")->publish]);'),before);
    const getMutation=await page.request.get(origin+'/wp-admin/admin-post.php?action=tb_setup_website');
    assert.equal(getMutation.status(),405);
    console.log('PASS missing nonce and GET cannot install theme or publish starter content');
    await page.locator('#tb-setup-submit').click(); await page.waitForLoadState('networkidle');
    await page.getByText('Trang chủ đã sẵn sàng.',{exact:true}).waitFor();
    assert.equal(wp('echo get_stylesheet();'),'thanh-binh');
    assert.equal(wp('echo wp_get_theme("thanh-binh")->get("Version");'),'1.1.0');
    assert.equal(wp('echo wp_count_posts("tb_product")->publish;'),'3');
    const attachment=Number(wp('echo get_option("tb_starter_pallet_attachment");')); assert.ok(attachment>0);
    assert.equal(wp('$p=get_page_by_path("pallet-go-theo-quy-cach",OBJECT,"tb_product");echo get_the_title($p)."|".get_post_thumbnail_id($p->ID);'),(originalTitle || 'Pallet gỗ kê hàng')+'|'+attachment);
    assert.ok(Number(wp('$locations=get_theme_mod("nav_menu_locations");echo count(wp_get_nav_menu_items($locations["primary"]));'))>=5);
    assert.equal(wp('echo get_option("show_on_front");'),'page');
    assert.equal(wp('echo (int)!!get_option("page_for_posts");'),'1');
    const installed=run(['exec','-T','wordpress','php','-r',"echo PHP_VERSION.'|'.hash_file('sha256','/var/www/html/wp-content/plugins/thanh-binh-orders/thanh-binh-orders.php');"]);
    assert.equal(installed,'7.4.30|'+createHash('sha256').update(fs.readFileSync('wp-content/plugins/thanh-binh-orders/thanh-binh-orders.php')).digest('hex'));
    console.log('PASS one setup button installs/activates bundled theme and creates pages/menu/products/articles/featured image on PHP 7.4.30');
    await page.goto(origin,{waitUntil:'networkidle'});
    assert.equal(await page.locator('.product-card').count(),3); assert.equal(await page.locator('.post-card').count(),3);
    assert.equal(await page.locator('.product-card').first().locator('h3').innerText(),originalTitle || 'Pallet gỗ kê hàng');
    const image=page.locator('.product-card').first().locator('img');
    await image.scrollIntoViewIfNeeded(); await image.evaluate(i=>i.loading='eager');
    await page.waitForFunction(()=>[...document.querySelectorAll('.product-card img')].every(i=>i.complete&&i.naturalWidth>0));
    assert.match(await image.getAttribute('src'),/uploads\/.*pallet-go-ke-hang/);
    for(const slug of ['tin-tuc','gioi-thieu','lien-he']) {
      const response=await page.goto(origin+'/'+slug+'/',{waitUntil:'networkidle'}); assert.equal(response.status(),200);
      if(slug==='tin-tuc')assert.ok(await page.locator('.post-card').count()>=3);
      if(slug==='lien-he')assert.equal(await page.locator('.quote-form').count(),1);
    }
    console.log('PASS complete homepage, featured photo, article listing, about and contact pages');
    const state=wp('global $wpdb;echo json_encode([wp_count_posts("post"),wp_count_posts("tb_product"),wp_count_posts("page"),get_option("tb_starter_pallet_attachment"),get_theme_mod("nav_menu_locations"),get_option("page_on_front"),get_option("page_for_posts")]);');
    wp('$p=get_page_by_path("pallet-go-theo-quy-cach",OBJECT,"tb_product"); update_post_meta($p->ID,"_tb_size","Quy cách đã chỉnh riêng"); set_theme_mod("tb_phone","0900000000");');
    await page.goto(origin+'/wp-admin/admin.php?page=tb-website');
    await page.locator('#tb-setup-submit').click(); await page.waitForLoadState('networkidle');
    await page.getByText('Trang chủ đã sẵn sàng.',{exact:true}).waitFor();
    assert.equal(wp('global $wpdb;echo json_encode([wp_count_posts("post"),wp_count_posts("tb_product"),wp_count_posts("page"),get_option("tb_starter_pallet_attachment"),get_theme_mod("nav_menu_locations"),get_option("page_on_front"),get_option("page_for_posts")]);'),state);
    assert.equal(wp('$p=get_page_by_path("pallet-go-theo-quy-cach",OBJECT,"tb_product");echo get_post_meta($p->ID,"_tb_size",true)."|".get_theme_mod("tb_phone");'),'Quy cách đã chỉnh riêng|0900000000');
    wp('$p=get_page_by_path("pallet-go-theo-quy-cach",OBJECT,"tb_product");update_post_meta($p->ID,"_tb_size","Theo kích thước kiện hàng");remove_theme_mod("tb_phone");');
    console.log('PASS repeated setup preserves content, settings, menu and image without duplicates');
    await page.goto(origin,{waitUntil:'networkidle'});
    fs.mkdirSync('.runtime/screenshots-complete',{recursive:true});
    await page.screenshot({path:'.runtime/screenshots-complete/desktop.png',fullPage:true});
    await page.setViewportSize({width:390,height:844});
    await page.screenshot({path:'.runtime/screenshots-complete/mobile.png',fullPage:true});
  } finally { await browser.close(); }
})().catch(e=>{console.error(e.message);process.exitCode=1;});
