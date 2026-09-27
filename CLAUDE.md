# dkoreiba.com — Blocksy child theme + DK Design System

WordPress site of a psychologist (dkoreiba.com). Parent theme Blocksy; everything
design-related lives in `blocksy-child/` in this repo. Pages are built from core
WordPress blocks + DK block styles/patterns. Goal: no plugins that do design work
we can do in the theme (GreenShift and WPCode design snippets are deactivated).

## Environments — read before touching anything
| | URL | How it changes |
|---|---|---|
| Staging | https://stage.dkoreiba.com | this repo via cPanel Git deploy; content edited in wp-admin |
| Production | https://dkoreiba.com | **only** Softaculous "Push to Live" from staging — done by the owner |

- Never deploy this repo, edit files, or change settings on production.
- Push to Live copies files + DB tables `posts`, `postmeta`, `options`, `terms*`.
  Never include Fluent Forms, PsyBooker, `comments`, `users` tables.
  Before: JetBackup backup. After: W3 Total Cache → Purge All Caches on production.
- Cloudflare (zone dkoreiba.com, Free) caches HTML at the edge: Cache Rule "DK: cache HTML pages 1h"
  (skips /wp-admin, /wp-login.php, /wp-json, calendar, logged-in/comment/postpass cookies,
  ?s= / ?p= / preview; query string ignored in the cache key) + Smart Tiered Cache,
  Crawler Hints (IndexNow) and Speed Brain (prefetch on click/tap) on.
  After Push to Live or any content change: Cloudflare → Caching → Purge Everything too,
  otherwise visitors see the old page for up to 1 h. Reason (2026-09-27): hosting has a
  2-core CPU limit; bursts of uncached requests queued for 15–25 s / Cloudflare 522.
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
5. `tools/check.sh --live` — checks that the W3TC minified JS on the live home page is not
   empty. Right after a purge W3TC can serve 0-byte minify files for a moment and
   browsers keep them for an hour (cache-control max-age=3600) → all site JS dead
   (tabs, theme button, video). If empty: W3TC → Performance → Minify cache flush, re-check.
   Test in the browser pane with a fresh query string — it may hold the empty files too.

Run `tools/check.sh` before pushing (PHP syntax — an error takes staging down; JS syntax;
CSS brace balance — a stray `}` silently drops the next rule).

## Architecture
```
blocksy-child/
  functions.php          enqueue CSS chain + JS, register block styles & pattern category
  assets/css/tokens.css  colours/fonts/spacing (--dk-*), light + dark site-wide; maps Blocksy vars
  assets/css/base.css    .dk-page scope, typography, text styles, buttons
  assets/css/layout.css  primitives: dk-wrap, dk-section, dk-stack*, dk-split*, dk-grid*
  assets/css/components.css  hero, tabs, band, stats, timeline, cards, price…
  assets/css/templates.css   Blocksy templates: post, blog archive, default page, search, 404
  assets/css/vendors.css Blocksy header/footer, TranslatePress, Fluent Forms
  assets/js/theme-init.js    inline in <head>: data-dk-theme + window.dkToggleTheme
  assets/js/dk.js            tabs (.dk-tabs > .dk-tabpanel), header theme button + CTA
  patterns/*.php         section patterns, category "DK: секции" (auto-registered)
```

Rules for new work:
- Every DK section is a full-width group with class `dk-page` (patterns already are).
  Component CSS is scoped to `.dk-page`; Blocksy templates (posts, archive, search,
  404, pages without DK sections) are styled in templates.css.
- Dark mode is site-wide (tokens.css, `html[data-dk-theme]` / OS preference). Any
  new colour must be a `--dk-*` token; Blocksy hex colours are mapped in tokens.css.
- Contrast check: light accent is #BA4917 (4.6:1 on paper) — keep small accent text
  ≥ 4.5:1 in both themes.
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

## Performance plugins (staging)
- Two optimisers are active: W3 Total Cache (page cache, JS/CSS minify, lazyload) and
  Debloat (CSS optimise/minify, remove unused block-library CSS, Google Fonts inline).
- Debloat CSS config (2026-09-27): Fix Render-Blocking CSS on, **Inline Optimized CSS on**,
  **Remove Unused CSS for theme + plugins on**, Remove-unused excludes:
  `themes/blocksy-child`, `plugins/gdpr-cookie-compliance`, `plugins/fluentform`,
  `plugins/psybooker`, `plugins/translatepress-multilingual`. Result: 0 external CSS,
  home HTML ≈ 227 KB (≈ 43 KB gzip), Blocksy main CSS 92 → 52 KB.
  "Always Keep Selectors" does NOT protect JS-added classes (tested: .dk-tab, .dk-acc-*,
  moove states were still removed) — exclude whole stylesheets instead. Any CSS whose
  classes appear only after JS runs must live in blocksy-child or an excluded plugin.
  Rollback: inline off, remove-unused theme/plugins off, Exclude Styles =
  themes/blocksy-child/assets/css, themes/blocksy/static, uploads/blocksy/css.
- Fonts: only used faces are loaded (DK_DS_FONTS in functions.php): Literata 400/500/400i
  at fixed opsz 36, Onest 400/500/600 — 161 KB instead of 407 KB. A new weight/style in
  CSS must be added to that URL, otherwise the browser fakes it.
- Home page first visit ≈ 285 KB / 27 requests (fonts 161, images ~51, HTML 48, JS 21).
- GDPR Cookie Compliance and Site Kit are deactivated on staging by the owner (for now).
- Lighthouse, home, mobile (local, cached page, 3 runs): 94/99/99, FCP 1.2 s, LCP 2.0 s.
  What mattered: YouTube facade (render_block core/embed → .dk-yt, iframe on click, no autoplay;
  was ~1.4 MB), LCP portrait eager + fetchpriority=high + real `sizes` (dk-circle),
  no opacity in the hero rise-in animation (it delayed LCP), logo `sizes`.
  Measure with `npx lighthouse@12 <url> --only-categories=performance --form-factor=mobile`
  (the public PSI API quota is often exhausted).
- Layout fingerprints must be taken with animations disabled
  (`*{animation:none!important}`) — .dk-hero and .dk-reveal use transforms.

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
- **Translations (TranslatePress, uk only at /ua/) match strings exactly.** TP splits text
  at inline links/emphasis and stores some entities encoded (`&#8212;`, `&hellip;`).
  Source of truth: `content/translations-uk.json` (RU original → UK, written by Claude, no
  machine translation). To apply: fetch the JSON from raw GitHub in a logged-in stage tab and
  POST `{pairs}` to `/wp-json/dk/v1/translations` (admin; updates by exact original, inserts
  missing rows, status 2). GET the same endpoint to look up the exact stored originals first.
  After editing page text, re-collect the changed strings and add pairs. Scope: home, about,
  contact, calendar, posts 888/886, header/footer/sidebar; blog posts stay untranslated.
  SiteSEO's `<title>`/meta are not translated by TP (prod too) — functions.php buffers
  `wp_head` on uk and swaps them from the same dictionary, so add title/meta strings as pairs.
- Contact (id 16) rebuilt on DK from `content/contact.html` (Fluent Forms form 3
  kept, styled in vendors.css); old version: revision 1244.
- Header: theme button = `[dk_theme_toggle]` shortcode in the Blocksy "Text" element
  (`[dk_lang_switch][dk_theme_toggle]`; `[dk_lang_switch]` = own flags-only switcher on `trp_custom_language_switcher()`, TranslatePress menu item hidden via CSS), CTA = Blocksy "Button" element; dk.js no
  longer injects markup. Customizer snapshot: `config/theme-mods.stage.json`.
- Calendar (id 1316) rebuilt from `content/calendar.html` (PsyBooker `[wppa_booking]`
  in a 520px wrap); old version: revision 1317. PsyBooker's timezone select stays
  white in dark mode (not styled yet).
- Footer widgets are core blocks from `content/footer.json` (widgets block-25..28);
  the old GreenShift widgets are in "Inactive widgets" (block-10/15/20/22/24).
- **GreenShift is gone from all published content** and the plugin is *deactivated*
  on staging (not deleted). The 8 posts that used it (871, 886, 888, 1118, 1210,
  1225, 1230, 1290) were converted in the block editor (GS text/heading/image/button →
  core paragraph/list/heading/image/buttons; word counts verified); previous content
  is in each post's revisions. Blog sidebar widget block-1 → block-29 (.dk-sidebar-cta).
  Drafts 970/1250/1251/1302/1411 still contain GreenShift — out of scope (owner's
  decision). Blog posts use Blocksy's default typography, not DK yet.
- Editor palette = Blocksy palette-color-1..8 + DK tokens (dk-*), via the
  `wp_theme_json_data_theme` filter in functions.php. Don't add a child theme.json
  palette: it makes WordPress drop Blocksy's editor-color-palette support.
- Desktop header: Blocksy centres the menu between two equal side columns and folds
  overflowing items into "More". Budget: menu ≈ 600 px; the CTA is hidden ≤1365 px so
  all six items fit at every desktop width. Adding anything to the header end column
  needs a re-check at 1181/1280/1366/1440 (fresh page load — Blocksy doesn't re-measure).
- Open: Blocksy palette (`colorPalette`) is overridden by tokens.css, not by settings;
  PsyBooker widget styling.
