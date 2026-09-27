# content/

Initial block markup for pages rebuilt on the DK design system, generated with
`tools/blocks.py`. The **database is the source of truth** once a page is published —
editors change pages in wp-admin, so these files can drift. Use them to (re)build
a page, not as a mirror.

Push to staging from a logged-in wp-admin tab (repo is public):
`fetch('https://raw.githubusercontent.com/koreyba/dkoreiba-theme/main/content/<file>.html')`
→ `POST /wp-json/wp/v2/pages/<id>` with `{content}` and the `X-WP-Nonce` header.

| File | Page |
|---|---|
| about.html | about (id 12) |
