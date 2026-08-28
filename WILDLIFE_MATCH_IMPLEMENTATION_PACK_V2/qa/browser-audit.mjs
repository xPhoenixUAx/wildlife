import { chromium } from 'file:///C:/Users/User/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright/index.mjs';

const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe' });
const pages = ['index.html','wildlife-removal.html','attic-cleanup-restoration.html','entry-point-sealing-prevention.html','privacy.html','terms.html','cookie-policy.html'];
const viewports = [[1440,900],[1024,900],[390,844],[360,800]];
const errors = [];

for (const file of pages) {
  for (const [width,height] of viewports) {
    const context = await browser.newContext({ viewport:{width,height}, reducedMotion:'reduce' });
    await context.addInitScript(() => localStorage.setItem('wm-cookie-choice','reject'));
    const page = await context.newPage();
    page.on('console', msg => { if (msg.type()==='error') errors.push(`${file} ${width}: console ${msg.text()}`); });
    page.on('pageerror', error => errors.push(`${file} ${width}: pageerror ${error.message}`));
    await page.goto(`http://127.0.0.1:8765/${file}`, {waitUntil:'networkidle'});
    const metrics = await page.evaluate(() => ({scrollWidth:document.documentElement.scrollWidth,innerWidth:window.innerWidth,h1:document.querySelectorAll('h1').length,broken:[...document.images].filter(i=>!i.complete||i.naturalWidth===0).map(i=>i.getAttribute('src'))}));
    if (metrics.scrollWidth > metrics.innerWidth + 1) errors.push(`${file} ${width}: horizontal overflow ${metrics.scrollWidth}-${metrics.innerWidth}`);
    if (metrics.h1 !== 1) errors.push(`${file} ${width}: ${metrics.h1} H1 elements`);
    if (metrics.broken.length) errors.push(`${file} ${width}: broken images ${metrics.broken.join(',')}`);
    await context.close();
  }
}

const menuContext = await browser.newContext({viewport:{width:390,height:844}});
await menuContext.addInitScript(() => localStorage.setItem('wm-cookie-choice','reject'));
const menuPage = await menuContext.newPage();
await menuPage.goto('http://127.0.0.1:8765/index.html',{waitUntil:'networkidle'});
await menuPage.locator('.menu-toggle').click();
if (await menuPage.locator('#mobile-menu').getAttribute('hidden') !== null) errors.push('mobile menu did not open');
await menuPage.keyboard.press('Escape');
if (await menuPage.locator('.menu-toggle').getAttribute('aria-expanded') !== 'false') errors.push('mobile menu did not close on Escape');
if (!(await menuPage.locator('.menu-toggle').evaluate(el=>el===document.activeElement))) errors.push('mobile menu did not return focus');
await menuContext.close();

const cookieContext = await browser.newContext({viewport:{width:390,height:844}});
const cookiePage = await cookieContext.newPage();
await cookiePage.goto('http://127.0.0.1:8765/index.html',{waitUntil:'networkidle'});
if (!(await cookiePage.locator('.cookie-banner').isVisible())) errors.push('cookie banner not visible without preference');
await cookiePage.locator('[data-cookie="reject"]').click();
await cookiePage.reload({waitUntil:'networkidle'});
if (await cookiePage.locator('.cookie-banner').isVisible()) errors.push('cookie preference did not persist');
await cookieContext.close();

await browser.close();
if (errors.length) { console.error(`FAIL\n${errors.map(e=>`- ${e}`).join('\n')}`); process.exit(1); }
console.log(`PASS: ${pages.length} pages × ${viewports.length} viewports, menu keyboard behavior, cookie persistence, image loads and console checks`);
