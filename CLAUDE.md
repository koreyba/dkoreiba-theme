# dkoreiba.com — Blocksy child theme + DK Design System

WordPress site of a psychologist (dkoreiba.com). Parent theme Blocksy; everything
design-related lives in `blocksy-child/` in this repo. Pages are built from core
WordPress blocks + DK block styles/patterns. Goal: no plugins that do design work
we can do in the theme (GreenShift and WPCode design snippets are being removed).

## Environments — read before touching anything
| | URL | How it changes |
|---|---|---|
| Staging | https://stage.dkoreiba.com | this repo via cPanel Git deploy; content edited in wp-admin |
| Production | https://dkoreiba.com | **only** Softaculous "Push to Live" from staging — done by the owner |

- Never deploy this repo, edit files, or change settings on production.
- Push to Live copies files + DB tables `posts`, `postmeta`, `options`, `terms*`.
  Never include Fluent Forms, PsyBooker, `comments`, `users` tables.
  Before: JetBackup backup. After: W3 Total Cache → Purge All Caches on production.
- Push to Live overwrites production content with staging content — anything edited
  on production after staging was cloned is lost.

## Deploy to staging
1. Commit and `git push` (`main`). Repo: github.com/koreyba/dkoreiba-theme (public;
   cPanel can't clone private repos without shell access). Local git config
   authenticates as `koreyba` via `gh auth token -u koreyba`.
2. cPanel → Git Version Control → `dkoreiba-theme` → Manage → Pull or Deploy →
   **Update from Remote**, then **Deploy HEAD Commit**.
   (`/home/koreyb03/repositories/dkoreiba-theme`; `.cpanel.yml` rsyncs `blocksy-child/`
   into `stage.dkoreiba.com/wp-content/themes/blocksy-child/`.)
3. wp-admin → Performance (W3 Total Cache) → **Purge All Caches**. W3TC minifies and
   inlines CSS/JS, so without a purge the old version is served.
4. Verify (below). Check the deployed version: `/wp-content/themes/blocksy-child/style.css`.

Lint PHP before pushing — a syntax error takes the whole staging site down:
`docker run --rm -v "$PWD/blocksy-child":/t -w /t php:8.2-cli sh -c 'for f in $(find . -name "*.php"); do php -l $f; done'`

## Architecture
```
blocksy-child/
  functions.php          enqueue CSS chain + JS, register block styles & pattern category
  assets/css/tokens.css  colours/fonts/spacing (--dk-*), light + dark (dark only on .dk-page)
  assets/css/base.css    .dk-page scope, typography, text styles, buttons
  assets/css/layout.css  primitives: dk-wrap, dk-section, dk-stack*, dk-split*, dk-grid*
  assets/css/components.css  hero, tabs, band, stats, timeline, cards, price…
  assets/css/vendors.css Blocksy header/footer, TranslatePress, Fluent Forms
  assets/js/theme-init.js    inline in <head>: data-dk-theme + window.dkToggleTheme
  assets/js/dk.js            tabs (.dk-tabs > .dk-tabpanel), header theme button + CTA
  patterns/*.php         section patterns, category "DK: секции" (auto-registered)
```

Rules for new work:
- Every DK section is a full-width group with class `dk-page` (patterns already are).
  All DK CSS is scoped to `.dk-page`, so legacy GreenShift pages are unaffected.
- Layout = primitives from `layout.css`. Don't add a page-specific grid class.
- Look of a core block = a registered block style (`is-style-dk-*`, see
  `functions.php`), not a free-form class. Component-internal parts (dk-stat-num,
  dk-price, dk-meta…) keep plain `dk-*` classes.
- A new reusable section → new file in `patterns/` (write markup with
  `tools/blocks.py`, it emits what the editor saves), then use it on pages.
  WordPress caches the pattern list per theme version; functions.php clears that
  cache when pattern files change, so no manual step is needed.
- Page content built by Claude goes to `content/<page>.html` (see content/README.md)
  and is pushed to staging via REST by fetching it from raw.githubusercontent.com.
- Before renaming/removing a class, search all page/post content via REST for it
  (content is in the DB, not in git) — see "Verifying on staging".
- Colours/fonts only via `--dk-*` tokens.

## Verifying on staging
- HTML contains `dk-tokens-css`, `dk-layout-css`, `dk-components-css`, `dk-theme-init`;
  dk.js is inside a W3TC minify bundle (`/wp-content/cache/minify/*.js`).
- Home page: tabs switch (6 tab buttons, one tab list), theme button toggles dark
  (`html[data-dk-theme]`), no duplicated header buttons, mobile (375px) layout OK.
- Block editor: DK styles in the Styles panel, patterns under "DK: секции",
  no "This block contains unexpected content" warnings.
- Page content can be read/updated via REST from a logged-in wp-admin tab:
  nonce from `/wp-admin/admin-ajax.php?action=rest-nonce`, header `X-WP-Nonce`.
  Page revisions allow rollback.

## State (2026-09-27)
- WPCode snippets 1402, 1403, 1410, 1412 (old copy of this design system) are
  **deactivated** on staging, not deleted.
- Home page (id 1157) is built from the DK patterns (hero, video, tabs, values-band,
  experience, pricing). Pre-refactor content: revision 1414.
- Catalog: private page "DK: каталог компонентов" (id 1419, /dk-catalog/) =
  styles + all patterns. Regenerate after pattern changes: concatenate pattern
  content (REST `/wp/v2/block-patterns/patterns`; `dk/styles` is Inserter:no, fetch
  `/wp-content/themes/blocksy-child/patterns/styles.php`) and update page 1419.
- About (id 12) rebuilt on DK from `content/about.html`; old GreenShift version:
  revision 1326.
- **Translations (TranslatePress, /ua/, /en/) match strings exactly.** Re-segmenting
  text (splitting a block into paragraphs) loses the translation for that string —
  about page UA/EN are partly untranslated after the migration. When migrating a
  page, either keep the original text blocks 1:1 or re-translate in TranslatePress.
- Contact (id 16) rebuilt on DK from `content/contact.html` (Fluent Forms form 3
  kept, styled in vendors.css); old version: revision 1244.
- Header: theme button = `[dk_theme_toggle]` shortcode in the Blocksy "Text" element
  (`[language-switcher][dk_theme_toggle]`), CTA = Blocksy "Button" element; dk.js no
  longer injects markup. Customizer snapshot: `config/theme-mods.stage.json`.
- Translations are knowingly out of date after migrations — to be redone later.
- Calendar (id 1316) rebuilt from `content/calendar.html` (PsyBooker `[wppa_booking]`
  in a 520px wrap); old version: revision 1317. PsyBooker's timezone select stays
  white in dark mode (not styled yet).
- Footer widgets are core blocks from `content/footer.json` (widgets block-25..28);
  the old GreenShift widgets are in "Inactive widgets" (block-10/15/20/22/24).
- All published *pages* are GreenShift-free. GreenShift is still used by 8 blog
  posts (280 blocks), incl. the service posts linked from the home pricing cards:
  /psychotherapy-and-coaching (post 888), /сrisis-consultation (post 886).
  Drafts 970/1250/1251/1302/1411 are out of scope (owner's decision).
- Editor palette = Blocksy palette-color-1..8 + DK tokens (dk-*), via the
  `wp_theme_json_data_theme` filter in functions.php. Don't add a child theme.json
  palette: it makes WordPress drop Blocksy's editor-color-palette support.
- Open: Blocksy palette (`colorPalette`) is overridden by tokens.css, not by settings;
  PsyBooker widget styling.
