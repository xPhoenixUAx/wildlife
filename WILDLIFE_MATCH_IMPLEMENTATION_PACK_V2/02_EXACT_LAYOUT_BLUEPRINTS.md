# EXACT LAYOUT BLUEPRINTS

All desktop measurements below are evaluated at a 1440×900 viewport. Scale fluidly until the 1600px maximum. Content begins after the left rail.

## Global layer model

```text
z0  section background
z1  photographic tiles
z2  ivory/green editorial slabs
z3  labels and navigation
z4  projecting CTA tabs
z5  mobile menu / cookie banner
```

Every section is a real CSS Grid. Absolute positioning is allowed only for notches, small accents and intentional panel overlap. Do not position the entire layout with arbitrary pixels.

## 01 Hero — reference 01 (LOCKED)

- Desktop height: `min(1000px, 100svh)`; minimum 760px.
- Main area grid after rail: 12 columns, rows `38% 15% 35% 12%`.
- Photo tiles in row 1: garden columns 1–3; wall columns 4–5; roof columns 6–9; interior columns 10–12.
- Row 3: outdoor garden columns 1–9; interior columns 10–12.
- Ivory headline band: row 2, columns 1–12, z2.
- Band internal grid: brand title 5 columns; statement 3 columns; actions 4 columns.
- Primary CTA is a green module inside the action area.
- Bottom navigation dock: row 4, columns 1–12.
- A green projecting request tile overlaps rows 3–4 near columns 9–10, as shown in the approved original. Do not remove it merely because another CTA exists in the band; preserve the visual module and give it a useful action or status purpose defined in content.
- The hero should show no more than one screen at 900px height plus a small next-section cue.

Required DOM parts:

```text
.site-rail
.hero
  .hero__photo--garden-top
  .hero__photo--wall
  .hero__photo--roof
  .hero__photo--interior-top
  .hero__band
  .hero__photo--garden-bottom
  .hero__photo--interior-bottom
  .hero__floating-request
  .hero__dock
```

## 02 Services — reference 02

- Minimum height: 860px.
- 12-column × 10-row grid.
- Heading block: columns 1–7, rows 1–3.
- Attic image: columns 7–12, rows 1–5.
- Wildlife image: columns 1–6, rows 4–10.
- Sealing image: columns 6–12, rows 6–10.
- Wildlife title slab overlaps columns 1–4 at row 4.
- Attic title slab overlaps columns 7–10 at row 5.
- Sealing title slab overlaps columns 10–12 at rows 8–10.
- Only the largest service gets a solid green CTA; the other links remain quiet.

## 03 How it works — reference 03

- Minimum height: 780px.
- Left track image: 4 columns, full height.
- Top heading slab: columns 5–12, rows 1–3.
- Central ivory process slab: columns 5–8, rows 4–10, overlaps both images by 32–56px.
- Provider inspection photo: columns 8–12, rows 4–10.
- Three steps are stacked in one slab with separators; never three cards.
- No visible step numbers.

## 04 Signs — reference 04

- Minimum height: 820px.
- Heading slab: columns 1–5, rows 1–4.
- Dominant exterior/garden photo: columns 4–12, rows 1–9.
- Detail vent photo: columns 1–4, rows 5–9.
- Nesting detail: columns 9–12, rows 6–9.
- Six labels use varied widths and overlap the photo. They remain HTML, not baked into an image.
- Terracotta projecting CTA overlaps the lower edge near columns 8–10.

## 05 Why it matters — reference 05

- Minimum height: 800px.
- Heading slab: columns 1–6, rows 1–4.
- Attic photo: columns 7–12, rows 1–6.
- Wildlife photo: columns 1–6, rows 5–10.
- Sealed-edge photo: columns 7–12, rows 7–10.
- Three information slabs vary in width.
- Terracotta manifesto panel sits near the center and overlaps all three photographic zones.

## 06 Prepare request — reference 06

- Minimum height: 760px.
- Heading slab: columns 1–8, rows 1–4.
- Safe-photo image: columns 9–12, rows 1–10.
- Top-down detail image: columns 1–8, rows 5–10.
- Checklist comprises two staggered ivory vertical bands, not six cards.
- Green request tab overlaps the two bands and the right photograph.

## 07 Aggregator role — reference 07

- Minimum height: 760px.
- Background conversation photo spans columns 4–11.
- Intro slab: columns 1–5, rows 1–7.
- Terracotta Advertise & Collaborate strip: columns 11–12, rows 1–8.
- Three unequal disclosure modules overlap the lower portion. On desktop they may read as a row, but widths are `36% 30% 34%`, with alternating green/ivory fills.
- These modules must not make claims of endorsement.

## 08 Form and footer — reference 08

- Top form area minimum height: 720px.
- Garden image: columns 1–5.
- Form: columns 6–12 on ivory background; not a floating rounded card.
- Form desktop grid: name/email 2 columns; ZIP/service 2 columns; textarea full; upload full; consent full.
- Upload area is a terracotta strip.
- Submit is a forest-green projecting module that overlaps the footer by 28–44px.
- Footer is full-width forest green after the left rail terminates; four unequal columns.
- Success message is hidden until a successful response.

## 09 Service pages — reference 09

All three service pages share structure, never images.

- Hero minimum height: 680px.
- Four interlocked zones: tall subject photo 3 columns; title slab 4 columns; exterior photo 5 columns; narrow detail strip 1–2 columns at edge.
- Green CTA overlaps the title slab and the lower image boundary.
- Situations section is an unequal mosaic.
- Inspection scope is a full-width terracotta band.
- Questions section uses editorial rows, not an accordion by default.
- Each service page contains at least five substantial sections plus footer.

## 10 Mobile — reference 10

Use the separate responsive rules in `04_RESPONSIVE_INTERACTIONS.md`. The mobile reference is binding for visual order and overlap, while written content remains authoritative.
