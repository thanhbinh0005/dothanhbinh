const assert = require('node:assert/strict');
const fs = require('node:fs');
const path = require('node:path');
const {createHash} = require('node:crypto');
const {execFileSync} = require('node:child_process');
const {chromium} = require('playwright');
const args = ['compose','-p','thanh-binh-php74','-f','compose.yaml','-f','compose.php74.yaml'];
const origin = 'http://localhost:8081';
const run = extra => execFileSync('docker', [...args,...extra], {encoding:'utf8',stdio:['ignore','pipe','pipe']}).trim();
function assertInstalledFile(relative) {
  const expected = createHash('sha256').update(fs.readFileSync(relative)).digest('hex');
  const actual = run(['exec','-T','wordpress','php','-r',`echo hash_file('sha256', '/var/www/html/${relative}');`]);
  assert.equal(actual, expected, 'Installed ZIP contents must match the newly packaged source');
}
(async () => {
  // Test the web server's PHP, rather than the separate WP-CLI container's runtime.
  execFileSync('docker', [...args,'exec','-T','wordpress','sh','-c','cat > /var/www/html/tb-php-version.php'], {
    input:'<?php header("Content-Type: text/plain"); echo PHP_VERSION;',stdio:['pipe','pipe','pipe']
  });
  const browser = await chromium.launch({executablePath:process.env.CHROMIUM_PATH || '/usr/bin/chromium',headless:true,args:['--no-sandbox']});
  const page = await browser.newPage();
  page.setDefaultTimeout(20000);
  try {
    const runtime = await page.request.get(origin+'/tb-php-version.php');
    assert.equal(await runtime.text(),'7.4.30');
    console.log('PASS web server runs exactly PHP 7.4.30');
    await page.goto(origin+'/wp-login.php');
    await page.locator('#user_login').fill('siteadmin');
    await page.locator('#user_pass').fill(fs.readFileSync('.runtime/php74-admin-password','utf8').trim());
    await page.locator('#wp-submit').click();
    await page.waitForURL(/wp-admin/);
    const plugins = run(['run','--rm','-T','cli','plugin','list','--field=name']).split('\n');
    if (plugins.includes('thanh-binh-orders')) {
      run(['run','--rm','-T','cli','plugin','deactivate','thanh-binh-orders']);
    }
    await page.goto(origin+'/wp-admin/plugin-install.php?tab=upload');
    await page.locator('#pluginzip').setInputFiles(path.resolve('dist/thanh-binh-orders.zip'));
    await page.locator('#install-plugin-submit').click();
    await page.waitForLoadState('networkidle');
    if (await page.locator('a[href*="overwrite=update-plugin"]').count()) {
      await page.locator('a[href*="overwrite=update-plugin"]').click();
      await page.waitForLoadState('networkidle');
    }
    assert.doesNotMatch(await page.locator('body').innerText(),/requires a newer version of PHP|Installation failed|Fatal error/i);
    const activatePlugin = page.getByRole('link',{name:'Activate Plugin',exact:true});
    if (await activatePlugin.count()) { await activatePlugin.click(); await page.waitForLoadState('networkidle'); }
    await page.goto(origin+'/wp-admin/plugins.php');
    const pluginRow = page.locator('tr[data-plugin="thanh-binh-orders/thanh-binh-orders.php"]');
    if (await pluginRow.locator('.activate a').count()) {
      await pluginRow.locator('.activate a').click();
      await page.waitForLoadState('networkidle');
    }
    assert.equal(run(['run','--rm','-T','cli','plugin','get','thanh-binh-orders','--field=status']),'active');
    assert.equal(run(['run','--rm','-T','cli','plugin','get','thanh-binh-orders','--field=version']),'1.0.1');
    assertInstalledFile('wp-content/plugins/thanh-binh-orders/thanh-binh-orders.php');
    console.log('PASS upload and activate plugin ZIP through WordPress on PHP 7.4.30');
    await page.goto(origin+'/wp-admin/theme-install.php?upload');
    if (!await page.locator('#install-theme-submit').isVisible()) {
      await page.locator('.upload-view-toggle').click();
    }
    await page.locator('#themezip').setInputFiles(path.resolve('dist/thanh-binh-theme.zip'));
    await page.locator('#install-theme-submit').click();
    await page.waitForLoadState('networkidle');
    if (await page.locator('a[href*="overwrite=update-theme"]').count()) {
      await page.locator('a[href*="overwrite=update-theme"]').click();
      await page.waitForLoadState('networkidle');
    }
    assert.doesNotMatch(await page.locator('body').innerText(),/requires a newer version of PHP|Installation failed|Fatal error/i);
    const activateTheme = page.getByRole('link',{name:'Activate',exact:true});
    if (await activateTheme.count()) { await activateTheme.click(); await page.waitForLoadState('networkidle'); }
    await page.goto(origin+'/wp-admin/themes.php');
    const themeLink = page.locator('a[href*="action=activate"][href*="stylesheet=thanh-binh"]');
    if (await themeLink.count()) {
      await page.goto(new URL(await themeLink.first().getAttribute('href'), origin).href);
    }
    assert.equal(run(['run','--rm','-T','cli','theme','get','thanh-binh','--field=status']),'active');
    assert.equal(run(['run','--rm','-T','cli','theme','get','thanh-binh','--field=version']),'1.0.1');
    assertInstalledFile('wp-content/themes/thanh-binh/style.css');
    console.log('PASS upload and activate theme ZIP through WordPress on PHP 7.4.30');
    await page.goto(origin+'/wp-admin/options-permalink.php');
    await page.locator('input[name="selection"][value="/%postname%/"]').check();
    await page.locator('#submit').click();
    await page.waitForLoadState('networkidle');
    console.log('PASS save permalinks through WordPress on PHP 7.4.30');
    await page.goto(origin+'/wp-admin/customize.php');
    await page.locator('#accordion-section-tb_business').waitFor();
    await page.locator('#accordion-section-tb_business').click();
    await page.locator('#customize-control-tb_company input').waitFor({state:'visible'});
    console.log('PASS company/logo/content Customizer loads on PHP 7.4.30');
  } finally {
    await browser.close();
    run(['exec','-T','wordpress','rm','-f','/var/www/html/tb-php-version.php']);
  }
})().catch(error=>{console.error(error.message);process.exitCode=1;});
