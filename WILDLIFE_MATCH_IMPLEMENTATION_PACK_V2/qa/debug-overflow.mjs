import { chromium } from 'file:///C:/Users/User/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright/index.mjs';
const browser=await chromium.launch({headless:true,executablePath:'C:/Program Files/Google/Chrome/Application/chrome.exe'});
const page=await browser.newPage({viewport:{width:360,height:800}});
await page.addInitScript(()=>localStorage.setItem('wm-cookie-choice','reject'));
await page.goto('http://127.0.0.1:8765/index.html',{waitUntil:'networkidle'});
console.log(await page.evaluate(()=>[...document.querySelectorAll('*')].map(el=>{const r=el.getBoundingClientRect();return {tag:el.tagName,cls:el.className,id:el.id,left:r.left,right:r.right,width:r.width}}).filter(x=>x.left<-.5||x.right>document.documentElement.clientWidth+.5).slice(0,30)));
await browser.close();
