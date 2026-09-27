<?php
/**
 * Blocksy Child — DK Design System loader.
 *
 * CSS:      assets/css/{tokens,base,layout,components,vendors}.css — front end and block editor.
 * Styles:   core block styles (is-style-dk-*) — registered below, styled in CSS.
 * Patterns: patterns/*.php — auto-registered by WordPress, category "dk".
 * JS:       assets/js/theme-init.js (inline in <head>), assets/js/dk.js (footer).
 */

if ( ! defined( 'WP_DEBUG' ) ) {
	die( 'Direct access forbidden.' );
}

define( 'DK_DS_FONTS', 'https://fonts.googleapis.com/css2?family=Literata:ital,opsz,wght@0,7..72,300..700;1,7..72,300..600&family=Onest:wght@400;500;600&display=swap' );

/** Version an asset by its modification time so caches refresh after each deploy. */
function dk_ds_ver( $rel ) {
	$path = get_stylesheet_directory() . '/' . $rel;
	return file_exists( $path ) ? (string) filemtime( $path ) : null;
}

/** Register design-system styles (shared by front end and editor). */
function dk_ds_register_styles() {
	$dir = get_stylesheet_directory_uri();
	wp_register_style( 'dk-fonts', DK_DS_FONTS, array(), null );
	$deps = array( 'dk-fonts' );
	foreach ( array( 'tokens', 'base', 'layout', 'components', 'vendors' ) as $name ) {
		$rel = "assets/css/{$name}.css";
		wp_register_style( "dk-{$name}", "{$dir}/{$rel}", $deps, dk_ds_ver( $rel ) );
		$deps = array( "dk-{$name}" );
	}
}

/** Front end: parent style, design system, behaviour script. */
add_action( 'wp_enqueue_scripts', function () {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	dk_ds_register_styles();
	wp_enqueue_style( 'dk-vendors' ); // pulls the whole chain via dependencies.
	wp_enqueue_script( 'dk-ds', get_stylesheet_directory_uri() . '/assets/js/dk.js', array(), dk_ds_ver( 'assets/js/dk.js' ), array( 'in_footer' => true, 'strategy' => 'defer' ) );
}, 20 );

/** Block editor canvas: same styles, so the editor looks like the site. */
add_action( 'enqueue_block_assets', function () {
	if ( ! is_admin() ) {
		return;
	}
	dk_ds_register_styles();
	wp_enqueue_style( 'dk-vendors' );
} );

/** Theme init must run before first paint. */
add_action( 'wp_head', function () {
	$file = get_stylesheet_directory() . '/assets/js/theme-init.js';
	if ( file_exists( $file ) ) {
		echo "<script id=\"dk-theme-init\">\n" . file_get_contents( $file ) . "</script>\n"; // phpcs:ignore WordPress.Security.EscapeOutput
	}
}, 1 );

/** Block styles: the design-system look of core blocks, picked in the editor's Styles panel. */
add_action( 'init', function () {
	$styles = array(
		'core/paragraph' => array(
			'dk-eyebrow' => 'DK: надзаголовок',
			'dk-label'   => 'DK: подпись',
			'dk-lead'    => 'DK: лид',
			'dk-muted'   => 'DK: второстепенный',
			'dk-pill'    => 'DK: плашка',
			'dk-tag'     => 'DK: тег',
			'dk-link'    => 'DK: акцентные ссылки',
		),
		'core/heading'   => array(
			'dk-display' => 'DK: крупный',
		),
		'core/group'     => array(
			'dk-card'        => 'DK: карточка',
			'dk-card-accent' => 'DK: карточка (акцент)',
			'dk-band'        => 'DK: тёмная лента',
			'dk-quote-card'  => 'DK: цитата',
		),
		'core/list'      => array(
			'dk-dots'     => 'DK: список с точками',
			'dk-values'   => 'DK: крупные строки',
			'dk-timeline' => 'DK: хронология',
			'dk-links'    => 'DK: ссылки-строки',
		),
		'core/button'    => array(
			'dk-arrow' => 'DK: со стрелкой',
		),
		'core/image'     => array(
			'dk-blob'    => 'DK: живая форма',
			'dk-rounded' => 'DK: скруглённый',
		),
		'core/embed'     => array(
			'dk-rounded' => 'DK: скруглённый',
		),
	);
	foreach ( $styles as $block => $list ) {
		foreach ( $list as $name => $label ) {
			register_block_style( $block, array( 'name' => $name, 'label' => $label ) );
		}
	}

	register_block_pattern_category( 'dk', array( 'label' => 'DK: секции' ) );
} );

/**
 * WordPress caches the theme's patterns/ file list until the theme version changes.
 * Drop that cache whenever a deploy adds, removes or edits a pattern file.
 */
add_action( 'init', function () {
	$files = glob( get_stylesheet_directory() . '/patterns/*.php' ) ?: array();
	$sig   = md5( implode( '|', array_map( function ( $f ) { return basename( $f ) . ':' . filemtime( $f ); }, $files ) ) );
	if ( get_option( 'dk_ds_patterns_sig' ) !== $sig ) {
		$theme = wp_get_theme();
		if ( method_exists( $theme, 'delete_pattern_cache' ) ) {
			$theme->delete_pattern_cache();
		}
		update_option( 'dk_ds_patterns_sig', $sig, false );
	}
}, 1 );

/**
 * [dk_theme_toggle] — light/dark switch, placed in the Blocksy header via its "Text"
 * element (Customizer → Header). Rendered only on pages built with DK sections,
 * because dark tokens apply only there. Click handling: assets/js/dk.js.
 */
add_shortcode( 'dk_theme_toggle', function () {
	$post = get_queried_object();
	if ( ! ( $post instanceof WP_Post ) || false === strpos( $post->post_content, 'dk-page' ) ) {
		return '';
	}
	return '<button type="button" class="dk-theme-btn" aria-label="Сменить тему">'
		. '<svg class="dk-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>'
		. '<svg class="dk-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20 14.5A8 8 0 1 1 9.5 4a6.5 6.5 0 0 0 10.5 10.5z"/></svg>'
		. '</button>';
} );

/**
 * GET /wp-json/dk/v1/theme-mods — saved Customizer values (Blocksy header/footer,
 * palette…), admin only, read only. Used to snapshot config into config/ in git.
 */
add_action( 'rest_api_init', function () {
	register_rest_route( 'dk/v1', '/theme-mods', array(
		'methods'             => 'GET',
		'permission_callback' => function () {
			return current_user_can( 'edit_theme_options' );
		},
		'callback'            => function () {
			$mods = get_theme_mods();
			ksort( $mods );
			return rest_ensure_response( $mods );
		},
	) );
} );
