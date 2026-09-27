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
  assets/css/vendors.css Blocksy header/footer, TranslatePress, GreenShift footer widgets
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
- A new reusable section → new file in `patterns/`, then use it on pages.
- Selectors marked `/* legacy: */` in components.css support old markup; delete them
  once no page uses those classes (search page content via REST first).
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
- Home page (id 1157) is on DK. Pages still on GreenShift: about (12), contact (16),
  calendar (1316), drafts 970/1250/1251/1302/1411.
- Open: header buttons are injected by dk.js — replace with Blocksy header elements;
  `theme.json` for tokens in the editor palette (check Blocksy compatibility first);
  export Blocksy Customizer settings into the repo.
