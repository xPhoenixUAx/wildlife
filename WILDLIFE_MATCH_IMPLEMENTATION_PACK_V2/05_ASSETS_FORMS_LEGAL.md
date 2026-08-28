# ASSETS, FORMS, CONFIG AND LEGAL

## Asset production

Generate or source local realistic photographs matching every visible tile. Do not reuse one hero across pages.

Required homepage asset subjects:

- `hero-garden.webp`: squirrel outdoors in landscaped yard, daylight.
- `hero-wall.webp`: bright residential siding and planting.
- `hero-roof.webp`: roof edge with bird safely outside.
- `hero-interior.webp`: warm sunlit living room, no animal.
- `service-wildlife.webp`: raccoon outdoors beside home.
- `service-attic.webp`: clean bright attic work.
- `service-sealing.webp`: properly sealed soffit/vent detail.
- distinct images for tracks, inspection, exterior clues, nesting material, attic insulation, request preparation, homeowner conversation and request-form garden.

Service pages require at least four unique images each.

Delivery:

- WebP, local files.
- Hero tiles 1200–1800px on long edge; content 800–1400px.
- Aim below 250KB each where practical without visible artifacts.
- Explicit width/height, `aspect-ratio`, `object-fit` and correct lazy loading.
- Only above-fold active hero assets use `fetchpriority="high"`.
- If external licensed photos are used, record page URL, creator, license and filename in `IMAGE_CREDITS.md`.

Do not hotlink, use watermarks, reuse reference PNG as a page background, or bake UI text into photographs.

## Config

`config/site-config.js` must control:

- site name and support line;
- company/legal operator display name;
- corporate email;
- postal address;
- form destination email;
- footer service/company/legal links;
- consent label;
- Advertise & Collaborate title and body;
- copyright year;
- success message.

Do not place entire page content in config.

## Forms

At least the homepage and each service page include a working form posting to `handler.php`.

Fields:

- full name, required;
- email address, required;
- ZIP code, required;
- service needed, required select;
- what are you noticing, required textarea;
- photo upload, optional;
- consent, required checkbox;
- honeypot, hidden from users.

No phone field.

Server requirements:

- POST only; reject other methods.
- Sanitize and validate every value server-side.
- CSRF token tied to PHP session.
- Rate-limit by session/IP where reasonably possible without a database.
- Upload allowlist JPG, PNG, WebP; validate MIME with `finfo`; maximum 5MB each and 3 files; randomize saved/attachment names; never execute uploads.
- Prevent header injection.
- Return JSON for fetch and safe redirect fallback for non-JS submission.
- Do not expose PHP warnings or destination email.

Exact success message:

> Thank you! We have successfully received your request. Our team will review your information and get back to you shortly.

## Cookie banner

- Essential-only default.
- `Accept optional`, `Reject optional`, `Manage preferences`.
- Persist choice locally.
- Do not load optional analytics before consent.
- If no optional scripts exist, state that clearly and keep implementation honest.

## Security and privacy copy

- Do not claim perfect security.
- Do not claim data is never shared; form consent explicitly allows sharing request details with potential independent providers.
- Explain retention at a configurable/general level, not an invented exact period.
- Explain user rights without claiming a law applies universally.
- Terms must include aggregator status, no provider endorsement, no guaranteed introduction, independent pricing/methods, user verification responsibilities and limitation language.
