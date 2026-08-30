(() => {
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;
  const parallaxBanner = document.querySelector('[data-parallax-cta]');

  if (!reduceMotion) {
    const root = document.documentElement;
    const motionItems = new Set();
    const entranceItems = new Set();
    root.classList.add('motion-enabled');

    const mark = (element, type = 'rise', delay = 0, entrance = false) => {
      if (!element || motionItems.has(element)) return element;
      element.dataset.motion = type;
      element.style.setProperty('--motion-delay', `${Math.min(delay, 320)}ms`);
      motionItems.add(element);
      if (entrance) entranceItems.add(element);
      return element;
    };

    const markGroup = (containerSelector, itemSelector, type = 'rise', step = 80) => {
      document.querySelectorAll(containerSelector).forEach((container) => {
        [...container.querySelectorAll(itemSelector)].forEach((item, index) => {
          mark(item, type, index * step);
        });
      });
    };

    mark(document.querySelector('.hero__copy'), 'left', 40, true);
    mark(document.querySelector('.hero__actions'), 'rise', 140, true);
    mark(document.querySelector('.hero__reassurance'), 'rise', 220, true);
    mark(document.querySelector('.hero__photo--garden-top'), 'image-right', 80, true);
    mark(document.querySelector('.service-hero__title'), 'left', 40, true);
    mark(document.querySelector('.service-hero__subject'), 'image-right', 90, true);
    mark(document.querySelector('.legal-hero'), 'rise', 40, true);

    document.querySelectorAll('main > section:not(.hero):not(.service-hero) > header').forEach((header) => {
      mark(header, 'rise');
    });

    markGroup('.services', ':scope > .service-tile', 'rise', 90);
    markGroup('.process__steps', ':scope > article', 'rise', 85);
    markGroup('.signs', ':scope > .signs__card', 'rise', 90);
    markGroup('.matters__topics', ':scope > article', 'rise', 80);
    markGroup('.visual-story__cards', ':scope > figure', 'rise', 100);
    markGroup('.prepare__list', ':scope > li', 'rise', 55);
    markGroup('.about__points', ':scope > article', 'rise', 80);
    markGroup('.faq__list', ':scope > .faq__item', 'rise', 55);
    markGroup('.service-guidance__steps', ':scope > article', 'rise', 75);
    markGroup('.service-record__content ul', ':scope > li', 'rise', 55);
    markGroup('.service-landscape__gallery', ':scope > figure', 'rise', 100);
    markGroup('.service-landscape__details', ':scope > article', 'rise', 65);
    markGroup('.service-expectations__grid', ':scope > article', 'rise', 75);
    markGroup('.questions__rows', ':scope > article', 'rise', 55);
    markGroup('.service-related__links', ':scope > a', 'rise', 90);
    markGroup('.situations ul', ':scope > li', 'rise', 55);
    markGroup('.scope-band ul', ':scope > li', 'rise', 55);
    markGroup('.site-footer', ':scope > div, :scope > nav, :scope > p', 'rise', 55);
    markGroup('.legal-content', ':scope > section', 'rise', 60);

    [
      ['.process__inspection', 'image-right'],
      ['.signs-cta-banner__inner', 'rise'],
      ['.matters__wildlife', 'image-left'],
      ['.matters__manifesto', 'rise'],
      ['.matters__safety', 'rise'],
      ['.visual-story__panorama', 'image-left'],
      ['.prepare__photo', 'image-right'],
      ['.prepare__cta', 'rise'],
      ['.about__photo', 'image-left'],
      ['.about__nonendorsement', 'rise'],
      ['.work-gallery__stage', 'image-left'],
      ['.faq__photo', 'image-right'],
      ['.request__photo', 'image-left'],
      ['.request__form', 'rise'],
      ['.situations > img', 'image-right'],
      ['.service-record__content > header', 'rise'],
      ['.service-record aside', 'rise'],
      ['.questions aside', 'rise'],
      ['.service-cta > div:first-child', 'left'],
      ['.service-cta__actions', 'right']
    ].forEach(([selector, type]) => mark(document.querySelector(selector), type));

    const reveal = (element) => element.classList.add('is-visible');
    const pendingItems = [...motionItems].filter((item) => !entranceItems.has(item));

    if ('IntersectionObserver' in window) {
      const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
          if (!entry.isIntersecting) return;
          reveal(entry.target);
          observer.unobserve(entry.target);
        });
      }, { threshold: 0.08, rootMargin: '0px 0px -10% 0px' });
      pendingItems.forEach((item) => observer.observe(item));
    } else {
      pendingItems.forEach(reveal);
    }

    requestAnimationFrame(() => requestAnimationFrame(() => {
      root.classList.add('motion-page-ready');
      entranceItems.forEach(reveal);
    }));
  }

  if (parallaxBanner && !reduceMotion) {
    let frame = 0;
    const updateParallax = () => {
      const rect = parallaxBanner.getBoundingClientRect();
      const viewportCenter = window.innerHeight / 2;
      const bannerCenter = rect.top + rect.height / 2;
      const offset = Math.max(-90, Math.min(90, (viewportCenter - bannerCenter) * 0.22));
      parallaxBanner.style.setProperty('--cta-parallax-y', `${offset}px`);
      frame = 0;
    };

    const requestParallaxUpdate = () => {
      if (frame) return;
      frame = requestAnimationFrame(updateParallax);
    };

    updateParallax();
    addEventListener('scroll', requestParallaxUpdate, { passive: true });
    addEventListener('resize', requestParallaxUpdate);
  }
})();

(() => {
  const gallery = document.querySelector('[data-work-gallery]');
  if (!gallery) return;

  const slides = [...gallery.querySelectorAll('[data-work-slide]')];
  const dots = [...gallery.querySelectorAll('[data-work-dot]')];
  const previous = gallery.querySelector('[data-work-prev]');
  const next = gallery.querySelector('[data-work-next]');
  const toggle = gallery.querySelector('[data-work-toggle]');
  const status = gallery.querySelector('[data-work-status]');
  const reduceMotion = matchMedia('(prefers-reduced-motion: reduce)').matches;

  let current = 0;
  let timer = 0;
  let visible = false;
  let userPaused = reduceMotion;
  let focused = false;
  let pointerStart = null;

  const stop = () => {
    clearInterval(timer);
    timer = 0;
  };

  const canPlay = () => visible && !userPaused && !focused && !document.hidden;

  const start = () => {
    stop();
    if (!canPlay() || slides.length < 2) return;
    timer = setInterval(() => show(current + 1, false), 5500);
  };

  const updateToggle = () => {
    if (!toggle) return;
    if (reduceMotion) {
      toggle.disabled = true;
      toggle.textContent = '—';
      toggle.setAttribute('aria-label', 'Automatic gallery disabled by reduced-motion preference');
      return;
    }
    toggle.textContent = userPaused ? '▶' : 'Ⅱ';
    toggle.setAttribute('aria-label', userPaused ? 'Start automatic gallery' : 'Pause automatic gallery');
  };

  function show(nextIndex, announce = true) {
    current = (nextIndex + slides.length) % slides.length;
    slides.forEach((slide, index) => {
      const active = index === current;
      slide.classList.toggle('is-active', active);
      slide.setAttribute('aria-hidden', String(!active));
    });
    dots.forEach((dot, index) => {
      if (index === current) dot.setAttribute('aria-current', 'true');
      else dot.removeAttribute('aria-current');
    });
    if (announce && status) status.textContent = `Image ${current + 1} of ${slides.length}`;
    start();
  }

  previous?.addEventListener('click', () => show(current - 1));
  next?.addEventListener('click', () => show(current + 1));
  dots.forEach((dot, index) => dot.addEventListener('click', () => show(index)));

  toggle?.addEventListener('click', () => {
    if (reduceMotion) return;
    userPaused = !userPaused;
    updateToggle();
    start();
  });

  gallery.addEventListener('focusin', () => {
    focused = true;
    stop();
  });
  gallery.addEventListener('focusout', () => {
    requestAnimationFrame(() => {
      focused = gallery.contains(document.activeElement);
      start();
    });
  });

  gallery.addEventListener('pointerdown', (event) => {
    if (event.pointerType === 'mouse') return;
    pointerStart = event.clientX;
  }, { passive: true });
  gallery.addEventListener('pointerup', (event) => {
    if (pointerStart === null) return;
    const distance = event.clientX - pointerStart;
    pointerStart = null;
    if (Math.abs(distance) < 48) return;
    show(current + (distance < 0 ? 1 : -1));
  }, { passive: true });

  document.addEventListener('visibilitychange', start);

  if ('IntersectionObserver' in window) {
    const observer = new IntersectionObserver(([entry]) => {
      visible = entry.isIntersecting;
      if (visible) start();
      else stop();
    }, { threshold: 0.2 });
    observer.observe(gallery);
  } else {
    visible = true;
  }

  updateToggle();
  show(0, false);
})();
