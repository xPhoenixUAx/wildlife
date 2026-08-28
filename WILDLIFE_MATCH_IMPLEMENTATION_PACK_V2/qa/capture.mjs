import { chromium } from 'file:///C:/Users/User/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright/index.mjs';
import fs from 'node:fs/promises';

const browser = await chromium.launch({ headless: true, executablePath: 'C:/Program Files/Google/Chrome/Application/chrome.exe' });
await fs.mkdir(new URL('./screenshots/', import.meta.url), { recursive: true });

for (const [name, width, height] of [
  ['hero-desktop', 1440, 900],
  ['hero-tablet', 1024, 900],
  ['hero-mobile', 390, 844],
  ['hero-mobile-min', 360, 800],
]) {
  const page = await browser.newPage({ viewport: { width, height }, deviceScaleFactor: 1 });
  await page.addInitScript(() => localStorage.setItem('wm-cookie-choice', 'reject'));
  await page.goto('http://127.0.0.1:8765/index.html', { waitUntil: 'networkidle' });
  await page.screenshot({ path: new URL(`./screenshots/${name}.png`, import.meta.url).pathname.replace(/^\/(.:)/, '$1') });
  if (name === 'hero-desktop' || name === 'hero-mobile') {
    await page.screenshot({ fullPage: true, path: new URL(`./screenshots/${name}-full.png`, import.meta.url).pathname.replace(/^\/(.:)/, '$1') });
  }
  if (name === 'hero-mobile') {
    await page.locator('.menu-toggle').click();
    await page.screenshot({ path: new URL('./screenshots/menu-mobile.png', import.meta.url).pathname.replace(/^\/(.:)/, '$1') });
  }
  await page.close();
}

for (const [slug, file] of [
  ['wildlife', 'wildlife-removal.html'],
  ['attic', 'attic-cleanup-restoration.html'],
  ['sealing', 'entry-point-sealing-prevention.html'],
]) {
  const page = await browser.newPage({ viewport: { width: 1440, height: 900 }, deviceScaleFactor: 1 });
  await page.addInitScript(() => localStorage.setItem('wm-cookie-choice', 'reject'));
  await page.goto(`http://127.0.0.1:8765/${file}`, { waitUntil: 'networkidle' });
  await page.screenshot({ path: new URL(`./screenshots/service-${slug}.png`, import.meta.url).pathname.replace(/^\/(.:)/, '$1') });
  await page.close();
}

await browser.close();
