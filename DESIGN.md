# Sadan Real Estate — Design system (v8.1, "cinematic editorial", multi-page, navy and gold)

The page tells a story as you scroll: a cinematic arrival, a statement you read
word by word, four services that stack like cards on a table, a walk sideways
through Dubai's neighbourhoods, then a calm, dark close. Direction informed by
current brokerage sites (Dubai Sotheby's International Realty, Engel & Völkers
Dubai, The Agency, fäm Properties) and award-style motion patterns; nothing is
copied from them. Colours come from the Sadan logo.

All values live as CSS variables at the top of `assets/css/style.css`.

## Colour

Taken from the Sadan logo: deep navy (#0A1E37) and a brushed gold that runs from
#8B6B36 to #DEAC67. Navy carries text, buttons and dark scenes; gold is the accent.

| Token | Value | Role |
|---|---|---|
| `--bg` / `--bg-2` | `#F7F4EE` / `#EEE8DD` | Ivory page / sand sections (process, card 2) |
| `--surface` | `#FFFFFF` | Finder bar, card 1 |
| `--ink` / `--ink-2` / `--ink-3` | `#0F1A2B` / `#4F5A6B` / `#868E9B` | Text, secondary text, inactive numbers |
| `--line` | `#DAD4C8` | Hairlines on light |
| `--navy` | `#0A1E37` | The logo navy: primary buttons, links, focus ring on light, map line |
| `--navy-deep` | `#12294A` | Card 4 |
| `--midnight` | `#08172B` | Dark scenes: intro, hero shade, areas, contact, footer, menu, card 3 |
| `--on-dark` / `--on-dark-2` | `#EEF1F5` / `#A9B4C4` | Text on midnight |
| `--gold-deep` | `#8B6A33` | Accent italics in headings on light (large text only) |
| `--gold` | `#B08446` | Small marks and numbers on light (decorative only) |
| `--gold-soft` | `#DDB16C` | Accent italics, buttons, cursor and focus ring on dark |
| `--gold-sheen` | gradient #8B6B36 → #DEAC67 → #A3763A → #D1A15B | The logo's brushed gold, used only on the small roof marks |

## Typography

- **Instrument Serif** for display, set very large (hero up to 10.5rem). Italic
  carries the accent phrase ("*our own.*").
- **Geist** for body and interface; uppercase micro labels with 0.16em tracking.
- Arabic: **Noto Naskh Arabic** for display, **IBM Plex Sans Arabic** for body,
  with smaller display sizes and taller line heights (`:lang(ar)` overrides).

Scale: `--fs-hero`, `--fs-mega` (contact), `--fs-h2`, `--fs-h3`, `--fs-lead`,
`--fs-body` 17px, `--fs-small` 15px, `--fs-micro` 12px.

## Space, shape, depth

4px base scale `--s-1`…`--s-10`; sections `clamp(96px, 12vw, 180px)`; content
width 1360px. Radius 10 / 18 / 32 / pill. One shadow (`--shadow`) for floating
things (finder, owners card). A 7% film-grain layer sits over the whole page.

## Signature pieces

- **Intro** (first visit per session, ~2s): the logo mark rises in from the bottom,
  "Sadan سدن" rises, a counter runs to 100, the curtain lifts.
- **Hero**: full-screen slideshow (3 photos, slow push-in, 6.5s each) with progress
  bars and captions; headline rises word by word.
- **Finder**: white bar overlapping the hero. Picks service, property and area,
  fills the enquiry form and glides there. Shows a WhatsApp button only when
  `WHATSAPP` is set in `main.js`.
- **Marquee**: community names, alternating solid and outlined italic; speeds up
  with scroll speed; runs the other way in Arabic.
- **Manifesto**: words light from 16% to full as you read; counters built only from
  facts on the page (4 services, 12 communities, 2 languages, 1 adviser).
- **Service stack**: four sticky cards (white, sand, midnight, deep navy); each one
  shrinks and darkens slightly as the next slides over it. Normal list on phones.
- **Areas**: pinned section; vertical scrolling moves the gallery sideways
  (reversed in Arabic), with image parallax, a progress line and a closing "Also
  covering" list. Phones, tablets and reduced motion get a native swipe rail.
- **Process**: sticky heading; the navy line fills and each step lights up.
- **Owners**: full-bleed photo with parallax and a floating ivory card.
- **Footer**: giant "Sadan [mark] سدن" wordmark that rises in, the mark opening upwards.

## Interaction details

Smooth scrolling (Lenis, loaded from jsDelivr; the page works without it). Buttons
fill from below on hover and lean towards the mouse. A gold cursor dot follows
the mouse and grows into an "Enquire" / "Email" label over photos and the email.
Header hides on scroll down and returns on scroll up. Live Dubai clock in the
header and contact block.

## Accessibility and motion

`prefers-reduced-motion` turns off the intro, smooth scrolling, pinning, parallax,
slideshow, word reveals and cursor; all content is visible without JavaScript.
Keyboard: skip link, visible focus rings (navy on light, gold on dark), menu
closes with Escape, focusing a neighbourhood card scrolls it into view.

## Imagery

Photos are Unsplash placeholders. Replace them with real listings when available,
keeping the same file names.

Logo files (cut from the supplied logo, transparent backgrounds):

| File | Use |
|---|---|
| `logo.webp` | Wide lockup (mark + Arabic + English), navy, for light backgrounds: header on light |
| `logo-light.webp` | Same lockup with navy turned ivory, gold kept: header on dark, footer |
| `logo-stack.webp` | The original stacked logo, navy: search engines (schema.org `logo`), sharing |
| `mark.webp` / `mark-light.webp` | The house-and-S mark alone: intro, footer wordmark |
| `favicon.png` / `apple-touch-icon.png` | Light mark on a navy square |

## v8: multi-page site

Pages: Home, Buy, Sell, Rent, Manage, Neighbourhoods (with the Sadan Line), six
neighbourhood guides, About, Contact, 404. Arabic lives at `/ar/...`; every page is
rendered on the server in both languages (better for search engines than switching
text in the browser).

Signature devices, used consistently across pages:

- **Roof transition**: moving between pages, the new page rises in the shape of the
  logo's house (cross-document View Transitions; browsers without support simply
  load the page normally).
- **Gable window** (`.gable`): photos in inner-page heroes, guide cards, the services
  mega menu and the Sadan Line peeks are framed in the roof shape, and open from the
  bottom like a house going up.
- **Arabic watermark**: each inner hero carries its Arabic name in huge outlined gold
  (شراء، بيع، تأجير، إدارة، the neighbourhood's name, دبي، سدن، تواصل).
- **The Sadan Line**: a metro-map diagram of the 12 communities, west to east.
  Waterfront stops above the line (gold rings), city stops on it (navy diamonds),
  villa communities below (navy rings). Filters by type; hover shows a photo.
  The map keeps west on the left in Arabic too; on phones it becomes a vertical route.
- **Next-page band**: service pages end with a huge link to the next service.

Inner page anatomy: dark hero (title, lead, action, gable photo, watermark) → light
content sections → dark enquiry or call-to-action band → footer.

Tools: mortgage estimate (Buy), net rent estimate (Manage), document checklists
(Sell, Rent) saved on the visitor's device. Calculators only do arithmetic on the
visitor's own figures; they make no claims about Sadan's fees or market prices.

## Properties (v8.3)

Sadan lists its own homes in the admin panel; they are stored in MySQL with their uploaded photos and
shown from there. Only *published* homes are public; drafts can be checked with a private preview link.

- **Properties page**: compact dark hero with the Arabic watermark عقارات and a white search bar that
  overlaps it (Buy / Rent toggle in display italic, keyword, area, type). A sticky filter column on the
  left (bedrooms, bathrooms, price, size, completion for sale or furnishing for rent) turns into a
  "More filters" panel on phones. Filters update results in place and keep the address shareable;
  without JavaScript they submit as a normal form. Cards: 3:2 photo (900 px WebP), type, off-plan and
  "Sadan pick" tags, photo count, price in display type, two-line title, gold pin, specs row.
- **Property page**: dark top with a five-photo grid (swipe rail on phones) and a full-screen lightbox
  (2000 px WebP), title and gold price; light body with key facts (including the Trakheesi permit and
  reference), description folded behind "Read more", amenities in the page language, and an
  OpenStreetMap map. A sticky dark request card (a floating bar on phones) posts to `request.php`.
  Published pages are indexed and listed in the sitemap.

## Admin panel (v8.3)

A working tool in the same brand: midnight sidebar (radial navy gradient, gold *New property* button,
gold-tinted active item), warm ivory canvas `#F4F2ED`, white cards with a 1px `#EEEAE2` border and a
soft shadow, Geist throughout with Instrument Serif only for the dashboard greeting.

- **Colour roles**: navy `#0A1E37` = primary buttons and selected chips; gold `#B08446`/`#DDB16C` =
  focus rings, today's bar, the save bar's button, "new" markers. Status colours: request New = gold,
  Contacted = blue, Viewing booked = violet, Closed = green, Spam = grey; listing Published = green,
  Draft = amber, Sold/Rented = blue, Archived = grey. Status is always a word too, never colour alone.
- **Components**: sticky top bar (title, breadcrumbs, search with `/`, page actions); stat cards
  (the *New requests* card turns dark navy when something is waiting); 14-day bar chart in CSS;
  segmented tabs with counts; property cards or a compact list (choice remembered); `⋯` menus built
  on `<details>` so they work without JavaScript; pill chips for bedrooms, bathrooms and furnishing;
  tick tiles for amenities; a switch for *Sadan pick*; toasts under the top bar.
- **Editor**: two columns (form + sticky side panel with status, publish checklist, activity).
  Photo grid with the cover spanning 2×2, drag to reorder plus ← → buttons for keyboard use,
  drop zone, per-photo upload progress. A dark save bar slides up when there are unsaved changes, and
  leaving the page warns first.
- **Phones**: the sidebar becomes a drawer; side panels move above the form; tables become rows with
  name, status and time; photo tools are always visible.
