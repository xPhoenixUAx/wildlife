# WILDLIFE MATCH — CODEX IMPLEMENTATION CONTRACT

## Outcome

Build a complete production-ready static website for **Wildlife Match**, an independent referral platform that may introduce homeowners to independent wildlife-removal providers.

This package deliberately contains both visual references and code scaffolding. Do not reinterpret the visual direction. Complete and refine the provided scaffold.

## Mandatory reading order

1. Inspect all PNG files in `references/` at original size.
2. Read `01_LOCKED_DESIGN_SYSTEM.md`.
3. Read `02_EXACT_LAYOUT_BLUEPRINTS.md`.
4. Read `03_CONTENT_AND_PAGE_MAP.md`.
5. Read `04_RESPONSIVE_INTERACTIONS.md`.
6. Read `05_ASSETS_FORMS_LEGAL.md`.
7. Read `06_VISUAL_QA_CONTRACT.md`.
8. Inspect every file in `starter/` before editing.

## Priority order when instructions appear to conflict

1. Written legal, aggregator, accessibility and form rules.
2. Exact content in `03_CONTENT_AND_PAGE_MAP.md`.
3. Layout and proportions in `02_EXACT_LAYOUT_BLUEPRINTS.md`.
4. `references/01-home-hero-LOCKED.png` for the global visual system.
5. The mapped section reference.
6. Existing starter code.
7. Incidental AI-generated text or icons visible inside reference images.

## Non-negotiable implementation rules

- HTML5, CSS3, vanilla JavaScript and PHP only.
- No React, Vue, Angular, Svelte, Tailwind, Bootstrap, page builders or build step.
- No Lenis. GSAP CDN is optional only for restrained entrance animation.
- Complete the website. Do not return a plan or partial scaffold.
- Use local WebP delivery images. Never hotlink.
- No phone numbers, phone icons, `tel:` links or phone fields anywhere.
- Do not invent testimonials, ratings, awards, licenses, provider counts or completed-project statistics.
- Never say Wildlife Match performs, supervises, inspects, prices or guarantees work.
- Every provider is independent. Users must verify licensing, insurance, methods, permits, written scope and warranties.
- The visual references are layouts, not moodboards. Preserve their composition.

## Required output files

```text
starter/
├── index.html
├── wildlife-removal.html
├── attic-cleanup-restoration.html
├── entry-point-sealing-prevention.html
├── privacy.html
├── terms.html
├── cookie-policy.html
├── handler.php
├── favicon.svg
├── README.md
├── IMAGE_CREDITS.md
├── config/site-config.js
├── css/base.css
├── css/home.css
├── css/service.css
├── css/legal.css
├── css/animations.css
├── js/main.js
├── js/animations.js
└── img/common, img/home, img/services
```

Homepage anchors may serve as About, How it works and Contact. Inner-page navigation must return to the correct `index.html#anchor`.

## Required build sequence

1. Inventory files and references; make no visual changes.
2. Implement shared tokens, fonts and left brand rail.
3. Implement only Homepage Hero and mobile menu.
4. Render and correct against references 01 and 10.
5. Implement homepage sections 2–8 one at a time and run mapped screenshot checks.
6. Implement the shared service template, then produce the three pages with distinct content and images.
7. Implement config, PHP forms, cookie consent and legal pages.
8. Run all checks from `06_VISUAL_QA_CONTRACT.md` and fix failures.

Do not postpone responsive behavior until the end. Complete desktop and mobile for each section before moving forward.
