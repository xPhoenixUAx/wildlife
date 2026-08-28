(() => {
  const cfg = window.SITE_CONFIG || {};
  document.querySelectorAll('[data-config]').forEach((el) => {
    const key = el.dataset.config;
    if (cfg[key] != null) el.textContent = cfg[key];
  });
  document.querySelectorAll('[data-config-href="email"]').forEach((el) => { if (cfg.email) el.href = `mailto:${cfg.email}`; });
  if (cfg.disclaimer) document.querySelectorAll('.site-footer').forEach((footer) => {
    if (footer.querySelector('.site-footer__disclaimer')) return;
    const disclaimer = document.createElement('p');
    disclaimer.className = 'site-footer__disclaimer';
    const label = document.createElement('strong');
    label.textContent = 'Disclaimer:';
    disclaimer.append(label, document.createTextNode(` ${cfg.disclaimer}`));
    footer.querySelector('.site-footer__bottom')?.before(disclaimer);
  });
  if (cfg.footerLinks) document.querySelectorAll('.site-footer nav[aria-label]').forEach((nav) => {
    const links = cfg.footerLinks[nav.getAttribute('aria-label')];
    if (!links) return;
    const heading = nav.querySelector('strong')?.cloneNode(true);
    nav.replaceChildren();
    if (heading) nav.append(heading);
    links.forEach(([label, href]) => { const link=document.createElement('a'); link.textContent=label; link.href=href; nav.append(link); });
  });
  const requestedService = new URLSearchParams(window.location.search).get('service');
  if (requestedService) document.querySelectorAll('select[name="service"]').forEach((select) => {
    if ([...select.options].some((option) => option.value === requestedService)) select.value = requestedService;
  });
  if (document.querySelector('input[name="csrf_token"]')) fetch('csrf.php',{headers:{Accept:'application/json'}}).then((r)=>{if(!r.ok) throw new Error('CSRF unavailable'); return r.json();}).then((data)=>document.querySelectorAll('input[name="csrf_token"]').forEach((el)=>{el.value=data.token||'';})).catch(()=>{});

  const toggle = document.querySelector('.menu-toggle');
  const menu = document.querySelector('#mobile-menu');
  const menuClose = menu?.querySelector('.menu-close');
  let returnFocus = null;
  const focusable = () => [...menu.querySelectorAll('a,button,[tabindex]:not([tabindex="-1"])')];
  const closeMenu = () => {
    if (!toggle || !menu) return;
    menu.hidden = true; document.body.classList.remove('menu-open'); toggle.setAttribute('aria-expanded','false');
    returnFocus?.focus();
  };
  toggle?.addEventListener('click', () => {
    if (toggle.getAttribute('aria-expanded') === 'true') return closeMenu();
    returnFocus = document.activeElement; menu.hidden = false; document.body.classList.add('menu-open'); toggle.setAttribute('aria-expanded','true'); focusable()[0]?.focus();
  });
  menuClose?.addEventListener('click', closeMenu);
  menu?.addEventListener('click', (e) => { if (e.target.closest('a')) closeMenu(); });
  document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape' && !menu?.hidden) closeMenu();
    if (e.key === 'Tab' && !menu?.hidden) {
      const items = focusable(); if (!items.length) return; const first=items[0], last=items.at(-1);
      if (e.shiftKey && document.activeElement===first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && document.activeElement===last) { e.preventDefault(); first.focus(); }
    }
  });

  const serviceDropdowns = [...document.querySelectorAll('.services-dropdown')];
  const homeDock = document.querySelector('.hero__dock');
  const homeServicesDropdown = homeDock?.querySelector('.services-dropdown');
  const updateHomeDropdownDirection = () => {
    if (!homeDock || !homeServicesDropdown) return;
    const dockTop = homeDock.getBoundingClientRect().top;
    const stickyTop = parseFloat(getComputedStyle(homeDock).top) || 0;
    homeServicesDropdown.classList.toggle('services-dropdown--up', dockTop > stickyTop + 2);
  };
  updateHomeDropdownDirection();
  window.addEventListener('scroll', updateHomeDropdownDirection, { passive: true });
  window.addEventListener('resize', updateHomeDropdownDirection);
  serviceDropdowns.forEach((dropdown) => dropdown.addEventListener('toggle', () => {
    if (!dropdown.open) return;
    serviceDropdowns.forEach((other) => { if (other !== dropdown) other.open = false; });
  }));
  document.addEventListener('click', (e) => {
    serviceDropdowns.forEach((dropdown) => { if (!dropdown.contains(e.target)) dropdown.open = false; });
  });
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    serviceDropdowns.forEach((dropdown) => {
      if (!dropdown.open) return;
      dropdown.open = false;
      dropdown.querySelector('summary')?.focus();
    });
  });

  const banner = document.querySelector('.cookie-banner');
  const saved = localStorage.getItem('wm-cookie-choice');
  if (banner && !saved) banner.hidden = false;
  banner?.addEventListener('click', (e) => { const choice=e.target.dataset.cookie; if (!choice) return; localStorage.setItem('wm-cookie-choice',choice); banner.hidden=true; });

  document.querySelectorAll('.request__form').forEach((form) => form.addEventListener('submit', async (e) => {
    e.preventDefault(); const status=form.querySelector('.form-status');
    if (!form.reportValidity()) return;
    try { status.hidden=false; status.textContent='Sending your request…'; const res=await fetch(form.action,{method:'POST',body:new FormData(form),headers:{Accept:'application/json'}}); const data=await res.json(); if(!res.ok) throw new Error(data.message||'Unable to send request.'); status.textContent=data.message; status.dataset.state='success'; if(data.ok) form.reset(); }
    catch(err){ status.textContent=err.message||'Unable to send request.'; status.dataset.state='error'; }
  }));
})();
