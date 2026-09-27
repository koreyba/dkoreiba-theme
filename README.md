# dkoreiba-theme

Blocksy child theme for dkoreiba.com with the DK Design System.

## Structure
- `blocksy-child/` — deployed as `wp-content/themes/blocksy-child`
  - `assets/css/tokens.css` — colours, fonts, spacing; light site-wide, dark on redesigned pages (`.dk-page`)
  - `assets/css/base.css` — base typography, layout primitives, buttons
  - `assets/css/components.css` — `.dk-*` components (hero, tabs, band, stats, timeline, cards…)
  - `assets/css/vendors.css` — overrides for Blocksy header/footer, TranslatePress, GreenShift widgets
  - `assets/js/theme-init.js` — sets `data-dk-theme` before paint (inlined in `<head>`)
  - `assets/js/dk.js` — tabs, theme toggle, header CTA

## Deploy (staging only)
1. `git push` to GitHub.
2. cPanel → Git Version Control → this repo → Pull or Deploy → **Update from Remote** → **Deploy HEAD Commit**.
3. `.cpanel.yml` copies `blocksy-child/` into the staging site.
4. Production is updated only via Softaculous **Push to Live** — never deploy this repo to production directly.

Note: deploy copies files, it does not delete removed ones on the server.
