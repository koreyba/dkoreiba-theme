# dkoreiba-theme

Blocksy child theme for dkoreiba.com with the DK Design System.
Working rules, deploy steps and architecture: see [CLAUDE.md](CLAUDE.md).

## Structure
- `blocksy-child/` — deployed as `wp-content/themes/blocksy-child`
  - `assets/css/` — `tokens` → `base` → `layout` → `components` → `vendors`
  - `assets/js/` — `theme-init.js` (inline in `<head>`), `dk.js` (tabs, header buttons)
  - `patterns/` — section patterns (editor → Patterns → «DK: секции»)
  - `functions.php` — asset loading, block styles (`is-style-dk-*`), pattern category

## Deploy (staging only)
`git push` → cPanel Git Version Control → Update from Remote → Deploy HEAD Commit →
W3 Total Cache → Purge All Caches. Production only via Softaculous Push to Live.
