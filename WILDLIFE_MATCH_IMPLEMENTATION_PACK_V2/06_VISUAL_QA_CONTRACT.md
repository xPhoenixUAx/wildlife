# VISUAL QA CONTRACT

Codex must perform visual verification. Reading references is not sufficient.

## Required screenshot checkpoints

Render with browser screenshots at these exact sizes:

- Desktop: 1440×900.
- Tablet: 1024×900.
- Mobile: 390×844.
- Minimum mobile: 360×800.

## Stage gates

1. Shared rail + Hero → compare to references 01 and 10.
2. Services → compare to 02 and mobile continuation in 10.
3. How it works → 03.
4. Signs → 04.
5. Why it matters → 05.
6. Prepare request → 06.
7. About/aggregator → 07.
8. Form/footer → 08.
9. Each service page → structural comparison to 09.

Do not begin the next stage until the current section passes.

## Per-section comparison checklist

- The left rail is present at the correct width on desktop.
- Dominant image zones match the mapped reference within roughly 5% of width/height.
- Panel overlap direction and stacking match.
- Heading block begins in the same quadrant.
- CTA type, color and projected position match.
- No equal-card substitution occurred.
- Type scale and line breaks are visually close without clipping.
- Text remains HTML, not embedded in images.
- At 390px, overlap remains deliberate and readable.
- At 360px, there is no horizontal scroll.

## Automated checks

- No console errors.
- All internal links resolve.
- All required files exist.
- All images load locally.
- No `tel:` links or phone strings.
- No lorem ipsum, `TODO`, placeholder testimonials or empty anchors.
- One H1 per page.
- Form works with JS and has non-JS fallback.
- Keyboard menu and cookie controls work.
- Reduced-motion mode disables nonessential animation.

## Self-correction prompt

When a screenshot differs, do not explain the difference and move on. Patch CSS/HTML, render again and repeat until the mismatch is resolved. If an exact photographic match is impossible, preserve the reference's subject, crop direction, luminosity and negative-space role while keeping the CSS geometry exact.

## Completion report

The final response must list:

- pages completed;
- checks run;
- any factual config values still requiring the site owner's input;
- exact local entry file.

Do not call the build complete if any required page, form, image or legal page is missing.
