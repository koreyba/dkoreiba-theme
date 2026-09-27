<?php
/**
 * Blocksy Child — DK Design System loader.
 *
 * CSS:      assets/css/{tokens,base,layout,components,templates,vendors}.css — front end and editor.
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
	foreach ( array( 'tokens', 'base', 'layout', 'components', 'templates', 'vendors' ) as $name ) {
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
			'dk-circle'  => 'DK: круг',
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
 * element (Customizer → Header). Dark tokens apply site-wide. Click: assets/js/dk.js.
 */
add_shortcode( 'dk_theme_toggle', function () {
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

/**
 * Editor colour palette: append DK tokens to the palette Blocksy provides (it adds its
 * own palette-color-N via theme support; a child theme.json would replace it). Values are
 * CSS variables, so has-dk-*-color classes follow the light/dark tokens.
 */
add_filter( 'wp_theme_json_data_theme', function ( $theme_json ) {
	$data    = $theme_json->get_data();
	$palette = $data['settings']['color']['palette'] ?? array();
	if ( empty( $palette ) ) {
		// Blocksy registers palette-color-1..8 via add_theme_support( 'editor-color-palette' ),
		// which WordPress only uses when theme.json data has no palette — keep it.
		$support = get_theme_support( 'editor-color-palette' );
		$palette = is_array( $support ) && isset( $support[0] ) && is_array( $support[0] ) ? $support[0] : array();
	}
	$dk = array(
		'dk-ink'       => 'DK: текст',
		'dk-ink-2'     => 'DK: текст второстепенный',
		'dk-paper'     => 'DK: фон',
		'dk-surface'   => 'DK: поверхность',
		'dk-surface-2' => 'DK: поверхность 2',
		'dk-line'      => 'DK: линия',
		'dk-accent'    => 'DK: акцент',
		'dk-accent-soft' => 'DK: акцент мягкий',
		'dk-lake'      => 'DK: тёмная лента',
	);
	$slugs = wp_list_pluck( $palette, 'slug' );
	foreach ( $dk as $slug => $name ) {
		if ( ! in_array( $slug, $slugs, true ) ) {
			$palette[] = array( 'slug' => $slug, 'name' => $name, 'color' => 'var(--' . $slug . ')' );
		}
	}
	return $theme_json->update_with( array(
		'version'  => 3,
		'settings' => array( 'color' => array( 'palette' => $palette ) ),
	) );
} );

/**
 * [dk_lang_switch] — flags-only language switcher built on TranslatePress data
 * (trp_custom_language_switcher()). Current language = highlighted flag, others = links
 * to the same page in that language. Placed in the Blocksy header "Text" element.
 */
add_shortcode( 'dk_lang_switch', function () {
	if ( ! function_exists( 'trp_custom_language_switcher' ) ) {
		return '';
	}
	global $TRP_LANGUAGE;
	$out = '<nav class="dk-lang" aria-label="Язык сайта" data-no-translation>';
	foreach ( trp_custom_language_switcher() as $code => $lang ) {
		$img = sprintf( '<img src="%s" alt="%s" width="20" height="15" loading="eager" decoding="async">', esc_url( $lang['flag_link'] ), esc_attr( $lang['language_name'] ) );
		if ( $code === $TRP_LANGUAGE ) {
			$out .= '<span class="dk-lang__item is-current" aria-current="true" title="' . esc_attr( $lang['language_name'] ) . '">' . $img . '</span>';
		} else {
			$out .= sprintf( '<a class="dk-lang__item" href="%s" hreflang="%s" title="%s" data-no-translation>%s</a>', esc_url( $lang['current_page_url'] ), esc_attr( $lang['short_language_name'] ), esc_attr( $lang['language_name'] ), $img );
		}
	}
	return $out . '</nav>';
} );

/**
 * LCP / above-the-fold images.
 * - DK circle portraits (hero and page intros) are the LCP element: load eagerly with
 *   high priority, keep them out of W3TC lazyload (data-no-lazy), and give real sizes
 *   (they are shown at <=380px on phones, ~475px on desktop).
 * - The header logo is shown at 50/123px but was sized as a 512px image.
 */
add_filter( 'render_block_core/image', function ( $html, $block ) {
	$class = $block['attrs']['className'] ?? '';
	if ( false === strpos( $class, 'is-style-dk-circle' ) ) {
		return $html;
	}
	return preg_replace(
		'/<img(?![^>]*fetchpriority)/',
		'<img fetchpriority="high" loading="eager" decoding="async" data-no-lazy="1" sizes="(max-width: 860px) min(380px, calc(100vw - 32px)), 475px"',
		$html,
		1
	);
}, 10, 2 );

add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	if ( (int) get_theme_mod( 'custom_logo' ) === (int) $attachment->ID ) {
		$attr['sizes']        = '(max-width: 999px) 50px, 123px';
		$attr['loading']      = 'eager';
		$attr['data-no-lazy'] = '1';
	}
	return $attr;
}, 10, 2 );
