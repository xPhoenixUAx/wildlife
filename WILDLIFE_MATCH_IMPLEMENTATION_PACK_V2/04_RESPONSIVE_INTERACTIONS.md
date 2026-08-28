# RESPONSIVE AND INTERACTION RULES

## Breakpoints

- Wide: `1440px–1600px`, centered maximum canvas.
- Desktop: `1100px–1439px`, 12 columns.
- Tablet: `768px–1099px`, 8 columns; rail width 76–84px.
- Mobile: `360px–767px`, intrinsic single column with deliberate overlap.

Do not support below 360px. Prevent horizontal overflow at 360px.

## Mobile shell

- Desktop rail becomes a 68–72px horizontal forest-green brand bar.
- Logo stays left; square gold menu control right, minimum 48×48px.
- Full-screen menu is forest green, vertically scrollable, with large ivory navigation and terracotta request CTA.
- Trap focus, close on Escape, return focus, lock body scroll, preserve menu scroll on short devices.
- Disclosure inside menu: **Independent referral platform. Provider response and availability vary.**

## Hero mobile order

Reference 10 is binding.

1. Mobile brand bar.
2. Three-crop photo strip: garden wildlife, roof/bird, interior.
3. Overlapping ivory headline slab.
4. Primary and secondary actions, side by side when 390px permits and stacked at 360px if necessary.
5. Remaining hero photo continuation only if it adds meaning.

Do not reduce mobile hero to one generic image and centered text.

## Section mobile transformation

Preserve hierarchy by changing scale, not by turning everything into cards.

- Services: large wildlife photo full width; attic photo approximately 52% width aligned right; sealing detail full width; title slabs overlap edges by 16–24px.
- How it works: track photo, then one continuous ivory step slab, then provider photo. Step slab remains one component.
- Signs: dominant exterior photo first; labels appear as staggered full/partial-width editorial bands; two detail photos interrupt the list.
- Why it matters: manifesto panel remains a large terracotta slab between photo zones.
- Prepare: heading, photo, two staggered checklist bands, CTA tab.
- About: intro slab, conversation photo, disclosure modules alternating green/ivory; Advertise strip last.
- Form: image, heading, fields single-column, upload strip, consent, projecting submit, footer.

## Sticky behavior

- Desktop rail may remain sticky while each section scrolls.
- Mobile header is sticky with opaque green background; no blur.
- Anchor targets use `scroll-margin-top`.

## Motion

- Respect `prefers-reduced-motion`.
- Default reveal duration 500–750ms; translate maximum 20px.
- Photo tiles may reveal with a restrained clip-path wipe.
- Editorial slabs fade/translate with 50–90ms stagger.
- No parallax that changes reading order; no custom cursor; no smooth-scroll hijacking.

## Accessibility

- Skip link, semantic landmarks, logical heading order.
- Visible `:focus-visible` outline using gold against green and forest against ivory.
- Touch targets minimum 48×48px.
- Real buttons for menu, consent controls and form actions.
- Images receive meaningful alt only when informative; decorative crops use empty alt.
- Color is never the sole way to communicate error or selection.
