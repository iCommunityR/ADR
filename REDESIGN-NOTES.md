# African Disputes Resolution — 2026 Interface Redesign

This build keeps the existing PHP/database/admin architecture and replaces the public-facing presentation layer with a modern legal-research interface.

## Main changes

- Single sticky navigation bar with compact search, research workspace and language controls.
- Search-led homepage hero with integrated live platform metrics.
- New research navigator for countries, case law, regional regimes and institutions.
- Research-path navigation replacing the old dense directory-style homepage layout.
- Regional ADR frameworks promoted to a dedicated homepage rail.
- Country discovery redesigned around regions and selected jurisdictions.
- Homepage region links now pre-select the corresponding region in the country directory.
- Recent additions redesigned as a clean research/update stream rather than newspaper cards.
- New saved-research and institutional-access callout.
- Simplified modern footer; the previous Pan-African interface wording is no longer surfaced.
- Countries, regimes, case law, institutions, forms and research pages inherit the new visual system.
- Responsive layouts for desktop, tablet and mobile.
- English, French, Arabic, Portuguese and Swahili remain supported; new redesign-specific country-discovery copy is translated.

## Files changed / added

- `index.php`
- `assets/app.js`
- `assets/modern.css` (new)
- `includes/i18n.php`

The new stylesheet is deliberately loaded after the existing styles so current backend/page functionality can remain intact while the interface is upgraded safely.

## Reference palette — 7 August 2026
- Brand name updated to **African Disputes Resolution**.
- Main dark background changed to the supplied reference blue `#003567`.
- Primary accent changed to turquoise `#16C1BA`.
- Dark-surface text now uses soft white/blue-white instead of heavy dark text.
- Header changed to the same deep-blue family as the reference image, with white navigation and turquoise active states.
- Homepage hero, Coverage at a glance, country/case hero areas, research CTA and footer now use the unified reference palette.
- Coverage at a glance no longer uses the previous brown/sand treatment.
- Option D typography remains in place: medium headings with lighter/regular body text.

## Floating cards and brand refresh

- Added a platform-wide floating card system using turquoise accent borders, layered shadows, and gentle hover lift.
- Applied the card treatment to homepage research paths, regional frameworks, country cards, recent updates, search/filter panels, case results, institutions, research folders, subscription plans, document/case fact panels, and admin dashboard/form cards.
- Replaced the previous logo mark with a new balance-scale + Africa emblem in the current `#003567` / `#16C1BA` / `#F7F8F5` palette.
- Added `assets/logo-mark.svg` for the public header, footer, admin login and admin sidebar.
- Rebuilt the favicon assets: `assets/favicon.svg`, `assets/favicon-128.png`, and root `favicon.ico`.
## Warm Gold card accent update
- Floating-card borders now use Cool Teal `#42D6D0` to complement the rich-purple card surfaces.
- Hover borders use a stronger gold tone while retaining the existing elevation/shadow system.
- Selected card icons use a restrained pale-gold background and darker gold glyphs.
- Deep blue remains the primary background and turquoise remains the brand/CTA accent, producing a three-color system rather than an all-blue interface.


## Countries regional filter fix — 8 Aug 2026
- Regional controls now use real `?region=` links with server-side filtering as a fallback.
- JavaScript keeps instant in-page filtering and synchronizes the selected region with the URL.
- Country assets are cache-busted for this release so browsers load the updated filter code immediately.
- Region controls were restyled from the legacy dark navy treatment to a lighter blue pill system with a medium-blue active state and slightly darker hover state.
