# Sadan Real Estate — Design notes (v5, "modern gable")

## The idea

The house-roof (gable) shape from the logo is the page's one signature device.
Photos sit in gable-shaped windows, the hero repeats the logo's "small house +
big house + swoosh" arrangement at full size, the neighbourhoods form a street
of gables at different heights, and the contact block has a roof-peak top edge.
Everything else stays plain so that shape is what people remember.

## Colour (from the logo)

| Token | Value | Role |
|---|---|---|
| `--paper` / `--paper-deep` | `#F1F5EE` / `#E6EDE3` | Soft green page wash (with subtle gold/emerald radial atmosphere) |
| `--emerald` | `#0D5B36` | Headlines, primary button, contact block, rules |
| `--deep` | `#072A1A` | "How it works" section |
| `--text` / `--text-2` | `#1A2921` / `#4A5A51` | Body text / secondary text |
| `--gold` | `#C8992F` | Lines, gable outlines, small markers (never body text on paper) |
| `--gold-light` | `#E8C766` | Gold buttons (dark text), gold text on dark green |
| `--line` | `#D0DCD2` | Hairlines |

## Type

- **Anybody** (variable width) for headlines. The four hero words use its width
  axis: the selected word stretches wide and fills emerald, the others stay
  narrow and outlined. Section headings use width 78, weight 750, sentence case.
- **Onest** for body and interface text, 17px, line-height 1.65.

## Layout

Left-aligned throughout, content width 1280px. Square corners everywhere
(the gable is the only "shape"). No cards. Soft button lift only — no heavy
drop shadows on content blocks.

Sticky header turns into a frosted glass bar after a short scroll so navigation
stays available without competing with the hero.

## Components

- Hero service switch: BUY / SELL / RENT / MANAGE are tabs (click, hover or
  arrow keys). They swap the photo in the large gable and the detail panel.
  On small screens the words sit in a horizontal wrap for easier tapping.
- Street: six gable photos of different heights on a gold ground line;
  scrolls sideways on smaller screens; photos ease-scale on hover.
- Steps: numbered because it is a real sequence; small gold house markers on the line.
- Contact: a fill-in-the-sentence form (name, service, property type, area,
  email, optional phone and note). Posts to `contact.php`.

## Motion

Load: houses rise, swoosh sweeps, service words fade up. Scroll: section
blocks reveal once as they enter view. Interaction: word stretch, photo
crossfade, panel fade, button lift, area photo zoom. Reduced-motion is
respected (reveals appear immediately; load animations off).

## Logo and photos

`assets/img/logo.webp` (transparent). Use on light backgrounds only.
Photos are Unsplash (free licence); swap for real listing photos when available.
