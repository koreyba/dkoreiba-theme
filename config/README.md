# config/

Snapshot of Customizer settings (theme mods: Blocksy header/footer builder, palette,
socials…) — they live in the database, not in the theme files. Used for review,
diffing and manual rollback; nothing reads these files automatically.

Refresh from a logged-in wp-admin tab on staging:
`fetch('/wp-json/dk/v1/theme-mods', {headers: {'X-WP-Nonce': nonce}})` (endpoint in
functions.php, admin only), then save the JSON here with sorted keys.

Change Customizer values from the Customizer page:
`wp.customize('<setting>').set(value); wp.customize.previewer.save()`.
