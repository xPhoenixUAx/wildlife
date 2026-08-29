(() => {
  const cfg = window.SITE_CONFIG || {};
  const defaultBrand = 'Wildlife Match';
  const brand = String(cfg.brand || defaultBrand).trim() || defaultBrand;
  const configText = { ...cfg, companyName: cfg.company, copyrightYear: new Date().getFullYear() };
  document.querySelectorAll('[data-config]').forEach((el) => {
    const key = el.dataset.config;
    if (configText[key] != null) el.textContent = configText[key];
  });
  document.querySelectorAll('[data-config-href="email"]').forEach((el) => { if (cfg.email) el.href = `mailto:${cfg.email}`; });
  const pageName = window.location.pathname.split('/').pop() || 'index.html';
  const pageTitle = cfg.pageTitles?.[pageName];
  document.title = pageTitle
    ? String(pageTitle).split('{brand}').join(brand)
    : document.title.split(defaultBrand).join(brand);

  const replaceBrand = (value) => String(value).split(defaultBrand).join(brand);
  if (brand !== defaultBrand) {
    const walker = document.createTreeWalker(document.body, NodeFilter.SHOW_TEXT, {
      acceptNode: (node) => ['SCRIPT', 'STYLE', 'TEXTAREA'].includes(node.parentElement?.tagName)
        ? NodeFilter.FILTER_REJECT
        : NodeFilter.FILTER_ACCEPT
    });
    const brandNodes = [];
    while (walker.nextNode()) brandNodes.push(walker.currentNode);
    brandNodes.forEach((node) => { node.nodeValue = replaceBrand(node.nodeValue); });
    document.querySelectorAll('[aria-label],[title],[alt]').forEach((el) => {
      ['aria-label', 'title', 'alt'].forEach((attribute) => {
        if (el.hasAttribute(attribute)) el.setAttribute(attribute, replaceBrand(el.getAttribute(attribute)));
      });
    });
    document.querySelectorAll('meta[content]').forEach((meta) => { meta.content = replaceBrand(meta.content); });
  }

  const brandParts = brand.split(/\s+/);
  const brandLead = brandParts.shift() || brand;
  const brandAccent = brandParts.join(' ');
  document.querySelectorAll('.rail-wordmark').forEach((wordmark) => {
    wordmark.replaceChildren();
    const lead = document.createElement('span');
    lead.textContent = brandLead;
    wordmark.append(lead);
    if (brandAccent) {
      wordmark.append(document.createTextNode(' '));
      const accent = document.createElement('em');
      accent.textContent = brandAccent;
      wordmark.append(accent);
    }
  });
  document.querySelectorAll('.footer-brand,.hero__brand').forEach((wordmark) => {
    wordmark.replaceChildren();
    const lead = wordmark.matches('.hero__brand') ? document.createElement('strong') : document.createTextNode(brandLead);
    if (lead.nodeType === Node.ELEMENT_NODE) lead.textContent = brandLead;
    wordmark.append(lead);
    if (brandAccent) {
      wordmark.append(document.createTextNode(' '));
      const accent = document.createElement('em');
      accent.textContent = brandAccent;
      wordmark.append(accent);
    }
  });

  if (cfg.logo) {
    document.querySelectorAll('.rail-mark img,.mobile-menu__head img,.cookie-banner__mark img').forEach((image) => { image.src = cfg.logo; });
    document.querySelectorAll('link[rel~="icon"]').forEach((icon) => {
      icon.href = cfg.logo;
      icon.removeAttribute('type');
    });
  }
  if (cfg.disclaimer) document.querySelectorAll('.site-footer').forEach((footer) => {
    if (footer.querySelector('.site-footer__disclaimer')) return;
    const disclaimer = document.createElement('p');
    disclaimer.className = 'site-footer__disclaimer';
    const label = document.createElement('strong');
    label.textContent = 'Disclaimer:';
    const disclaimerText = String(cfg.disclaimer).replace(/^Disclaimer:\s*/i, '');
    disclaimer.append(label, document.createTextNode(` ${disclaimerText}`));
    footer.querySelector('.site-footer__bottom')?.before(disclaimer);
  });
  const requestedService = new URLSearchParams(window.location.search).get('service');
  if (requestedService) document.querySelectorAll('select[name="service"]').forEach((select) => {
    if ([...select.options].some((option) => option.value === requestedService)) select.value = requestedService;
  });
  const refreshCsrfToken = () => {
    if (!document.querySelector('input[name="csrf_token"]')) return Promise.resolve();
    return fetch('handler.php?action=csrf', { headers: { Accept: 'application/json' } })
      .then((response) => { if (!response.ok) throw new Error('CSRF unavailable'); return response.json(); })
      .then((data) => document.querySelectorAll('input[name="csrf_token"]').forEach((el) => { el.value = data.token || ''; }))
      .catch(() => {});
  };
  refreshCsrfToken();

  const toggle = document.querySelector('.menu-toggle');
  const menu = document.querySelector('#mobile-menu');
  const menuClose = menu?.querySelector('.menu-close');
  const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const afterPaint = (callback) => requestAnimationFrame(() => requestAnimationFrame(callback));
  let returnFocus = null;
  let menuCloseTimer = null;
  const focusable = () => [...menu.querySelectorAll('a,button,[tabindex]:not([tabindex="-1"])')];
  const finishMenuClose = () => {
    if (!toggle || !menu) return;
    menu.hidden = true;
    menu.classList.remove('is-opening', 'is-closing');
    document.body.classList.remove('menu-open');
    returnFocus?.focus();
  };
  const closeMenu = () => {
    if (!toggle || !menu || menu.hidden || menu.classList.contains('is-closing')) return;
    clearTimeout(menuCloseTimer);
    menu.classList.remove('is-opening');
    menu.classList.add('is-closing');
    toggle.setAttribute('aria-expanded','false');
    menuCloseTimer = setTimeout(finishMenuClose, reduceMotion ? 0 : 360);
  };
  toggle?.addEventListener('click', () => {
    if (toggle.getAttribute('aria-expanded') === 'true') return closeMenu();
    clearTimeout(menuCloseTimer);
    returnFocus = document.activeElement;
    menu.classList.remove('is-closing');
    menu.classList.add('is-opening');
    menu.hidden = false;
    document.body.classList.add('menu-open');
    toggle.setAttribute('aria-expanded','true');
    afterPaint(() => {
      if (menu.hidden || menu.classList.contains('is-closing')) return;
      menu.classList.remove('is-opening');
      focusable()[0]?.focus();
    });
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
  const dropdownTimers = new WeakMap();
  const finishDropdownClose = (dropdown, restoreFocus = false) => {
    dropdown.open = false;
    dropdown.classList.remove('is-opening', 'is-closing');
    dropdownTimers.delete(dropdown);
    if (restoreFocus) dropdown.querySelector('summary')?.focus();
  };
  const closeDropdown = (dropdown, restoreFocus = false) => {
    if (!dropdown.open || dropdown.classList.contains('is-closing')) return;
    clearTimeout(dropdownTimers.get(dropdown));
    dropdown.classList.remove('is-opening');
    dropdown.classList.add('is-closing');
    const timer = setTimeout(() => finishDropdownClose(dropdown, restoreFocus), reduceMotion ? 0 : 240);
    dropdownTimers.set(dropdown, timer);
  };
  const openDropdown = (dropdown) => {
    clearTimeout(dropdownTimers.get(dropdown));
    serviceDropdowns.forEach((other) => { if (other !== dropdown) closeDropdown(other); });
    dropdown.classList.remove('is-closing');
    dropdown.classList.add('is-opening');
    dropdown.open = true;
    afterPaint(() => dropdown.classList.remove('is-opening'));
  };
  serviceDropdowns.forEach((dropdown) => dropdown.querySelector('summary')?.addEventListener('click', (e) => {
    e.preventDefault();
    if (!dropdown.open || dropdown.classList.contains('is-closing')) openDropdown(dropdown);
    else closeDropdown(dropdown);
  }));
  document.addEventListener('click', (e) => {
    serviceDropdowns.forEach((dropdown) => { if (!dropdown.contains(e.target)) closeDropdown(dropdown); });
  });
  document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    serviceDropdowns.forEach((dropdown) => {
      if (!dropdown.open) return;
      closeDropdown(dropdown, true);
    });
  });

  const mobileServiceDropdowns = [...document.querySelectorAll('.mobile-services')];
  const mobileDropdownTimers = new WeakMap();
  const finishMobileDropdownClose = (dropdown) => {
    dropdown.open = false;
    dropdown.classList.remove('is-closing');
    mobileDropdownTimers.delete(dropdown);
  };
  mobileServiceDropdowns.forEach((dropdown) => {
    const summary = dropdown.querySelector('summary');
    const links = dropdown.querySelector('.mobile-services__links');
    summary?.addEventListener('click', (e) => {
      e.preventDefault();
      clearTimeout(mobileDropdownTimers.get(dropdown));
      if (!dropdown.open || dropdown.classList.contains('is-closing')) {
        dropdown.open = true;
        dropdown.classList.remove('is-closing');
        dropdown.style.setProperty('--mobile-services-height', `${links?.scrollHeight || 360}px`);
        afterPaint(() => {
          if (dropdown.open && !dropdown.classList.contains('is-closing')) dropdown.classList.add('is-expanded');
        });
        return;
      }
      dropdown.classList.remove('is-expanded');
      dropdown.classList.add('is-closing');
      const timer = setTimeout(() => finishMobileDropdownClose(dropdown), reduceMotion ? 0 : 340);
      mobileDropdownTimers.set(dropdown, timer);
    });
  });

  const banner = document.querySelector('.cookie-banner');
  const saved = localStorage.getItem('site-cookie-choice');
  if (banner && !saved) banner.hidden = false;
  banner?.addEventListener('click', (e) => { const choice=e.target.dataset.cookie; if (!choice) return; localStorage.setItem('site-cookie-choice',choice); banner.hidden=true; });

  const formModal = document.querySelector('#form-modal');
  const modalEyebrow = formModal?.querySelector('[data-form-modal-eyebrow]');
  const modalTitle = formModal?.querySelector('[data-form-modal-title]');
  const modalMessage = formModal?.querySelector('[data-form-modal-message]');
  const modalAction = formModal?.querySelector('.form-modal__action');
  const showFormModal = (state, message = '') => {
    if (!formModal) return;
    const isSuccess = state === 'success';
    formModal.dataset.state = state;
    modalEyebrow.textContent = isSuccess ? 'Request received' : 'Submission problem';
    modalTitle.textContent = isSuccess ? 'Your request has been received' : 'We couldn’t send your request';
    modalMessage.textContent = message || (isSuccess
      ? 'Thank you. Your details were submitted successfully. A provider response is not guaranteed.'
      : 'Something went wrong while sending the form. Your entries are still here, so you can close this message and try again.');
    if (typeof formModal.showModal === 'function') {
      if (!formModal.open) formModal.showModal();
    } else {
      formModal.setAttribute('open', '');
    }
    requestAnimationFrame(() => modalAction?.focus());
  };
  formModal?.addEventListener('click', (e) => {
    if (!e.target.closest('[data-form-modal-close]') && e.target !== formModal) return;
    if (typeof formModal.close === 'function') formModal.close();
    else formModal.removeAttribute('open');
  });
  document.querySelectorAll('.request__form').forEach((form) => form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const status = form.querySelector('.form-status');
    const submit = form.querySelector('[type="submit"]');
    if (!form.reportValidity()) return;
    status.hidden = false;
    status.textContent = 'Sending your request…';
    delete status.dataset.state;
    form.setAttribute('aria-busy', 'true');
    submit.disabled = true;
    try {
      const response = await fetch(form.action, { method: 'POST', body: new FormData(form), headers: { Accept: 'application/json' } });
      const data = await response.json().catch(() => ({}));
      if (!response.ok || !data.ok) throw new Error(data.message || 'We could not send the request. Please try again later.');
      status.textContent = data.message || 'Your request was sent successfully.';
      status.dataset.state = 'success';
      showFormModal('success');
      form.reset();
      refreshCsrfToken();
    } catch (error) {
      const message = error.message || 'We could not send the request. Please try again later.';
      status.textContent = message;
      status.dataset.state = 'error';
      showFormModal('error', message);
    } finally {
      form.removeAttribute('aria-busy');
      submit.disabled = false;
    }
  }));
})();
