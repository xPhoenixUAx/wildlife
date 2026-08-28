# LOCKED DESIGN SYSTEM

## Approved direction

The exact approved direction is `references/01-home-hero-LOCKED.png`. It is the original complex version, not a simplified revision.

Its identity comes from all of the following working together:

- a continuous dark-green vertical brand rail;
- a photographic panorama divided into garden, exterior wall, roof and interior zones;
- an ivory content band that crosses the photographs;
- a large green projecting CTA module;
- a bottom navigation dock;
- layered photography and editorial panels;
- warm, friendly daylight rather than fear-based imagery;
- classic editorial serif typography paired with clean sans-serif UI.

Removing any three of these features constitutes a redesign and is forbidden.

## Tokens

```css
:root {
  --forest-950: #0c3b24;
  --forest-900: #11492d;
  --forest-800: #1d5a36;
  --forest-700: #34713f;
  --ivory-50: #fbf8ef;
  --ivory-100: #f5f0e4;
  --ivory-200: #e8e0cf;
  --terracotta: #c85d38;
  --terracotta-dark: #a9472c;
  --gold: #e2b94e;
  --ink: #163a2a;
  --muted: #627064;
  --line: rgba(20, 59, 42, .24);
  --white: #fffdf8;
  --success: #2f6a3b;
  --error: #a43b32;
  --rail-w: clamp(76px, 6.5vw, 104px);
  --gutter: clamp(16px, 3vw, 48px);
  --section-space: clamp(80px, 8vw, 136px);
  --max: 1600px;
}
```

Do not introduce blue, purple, neon green, glass blur, gradients, black brutalism or rounded card palettes.

## Typography

- Display/editorial: `Fraunces`, weight 600–700, optical size enabled.
- Body/UI: `Manrope`, weight 400–700.
- Brand may use Fraunces or the same editorial serif.
- Desktop display: `clamp(3.1rem, 5.2vw, 5.6rem)`, line-height `.96–1.02`.
- Section title: `clamp(2.5rem, 4.4vw, 4.8rem)`, line-height `1.02`.
- Body: `clamp(1rem, .35vw + .92rem, 1.18rem)`, line-height `1.55`.
- No condensed fonts, all-caps paragraph text or decorative script.

## Corners, borders and shadows

- Default radius: `0`.
- Form controls may use `2px`.
- Floating tabs may have one small triangular notch made with a pseudo-element.
- Shadows only on overlapping editorial panels: `0 14px 34px rgba(18,53,35,.14)`.
- Do not wrap every text block in a shadowed card.

## Brand rail

Desktop rail is not an ordinary header.

- Width: `--rail-w`.
- Position: sticky or fixed at left; full viewport height.
- Dark forest background.
- Gold leaf/house mark at top.
- Vertical wordmark centered.
- Gold hairline and support text near bottom.
- Main content receives `margin-inline-start: var(--rail-w)`.

Mobile converts the rail into a 68–72px horizontal brand bar. Do not retain a thin vertical strip on mobile.

## Photography

- Bright natural daylight and realistic North American homes.
- Wildlife remains outdoors in peaceful, believable situations.
- Show exterior envelope details, clean attic work and sealing details without shock imagery.
- Avoid glowing eyes, animals trapped indoors, severe contamination close-ups, carcasses, cages or weapons.
- Each page uses distinct images. Shared layout does not mean shared assets.

## CTA language

- Primary CTA: solid forest-green projecting rectangular tab, typically 52–64px high.
- Secondary CTA: ivory outlined rectangle or quiet underlined text, depending on mapped reference.
- Terracotta is used for emphasis and photo-upload strips, not every button.
- Do not replace CTA tabs with pills, circles or generic arrow buttons.

## Anti-drift list

Do not:

- replace the left rail with a top navbar on desktop;
- flatten mosaics into a text-left/photo-right split;
- turn service choices, symptoms or steps into three equal cards;
- use a single background photo with text overlay for every section;
- simplify the hero to a normal banner;
- center all headings;
- add large animal icons or paw-print decoration;
- add decorative arrows not shown in the written blueprint;
- reproduce the accidental text, icons or spelling inside AI references when the written spec differs.
