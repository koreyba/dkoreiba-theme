<?php
/**
 * Blocksy Child — DK Design System loader.
 *
 * CSS:  assets/css/{tokens,base,components,vendors}.css — front end and block editor.
 * JS:   assets/js/theme-init.js (inline in <head>), assets/js/dk.js (footer).
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
	foreach ( array( 'tokens', 'base', 'components', 'vendors' ) as $name ) {
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
