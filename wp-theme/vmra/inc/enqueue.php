<?php
/**
 * Asset (CSS/JS) registration for the VMRA theme.
 *
 * The static site loads:
 *   /fonts/fonts.css   - @font-face declarations for Anton, Space Grotesk, JetBrains Mono, Archivo Black
 *   /css/shell.css     - Global shell: nav, mobile menu, banner, footer, grain+scanline overlays
 *   /css/home.css      - Homepage-only modules (only enqueued on front-page)
 *   /js/shell.js       - Mobile menu behavior (shared across all pages)
 *
 * Per-page CSS (schedule, standings, etc.) is enqueued conditionally.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'wp_enqueue_scripts', function () {

	// 1. Fonts — preload the two woff2 files used above the fold.
	wp_enqueue_style(
		'vmra-fonts',
		VMRA_THEME_URI . '/assets/fonts/fonts.css',
		array(),
		VMRA_THEME_VERSION
	);

	// 2. Shell — global chrome. MUST load after any page-local <style>
	//    blocks so shell rules win the cascade (same rule as the static site).
	wp_enqueue_style(
		'vmra-shell',
		VMRA_THEME_URI . '/assets/css/shell.css',
		array( 'vmra-fonts' ),
		VMRA_THEME_VERSION
	);

	// 3. Homepage-only CSS — only on the front page.
	if ( is_front_page() ) {
		wp_enqueue_style(
			'vmra-home',
			VMRA_THEME_URI . '/assets/css/home.css',
			array( 'vmra-shell' ),
			VMRA_THEME_VERSION
		);
	}

	// 4. Per-page CSS — enqueue each page's own stylesheet if present.
	$page_slug_map = array(
		'schedule'     => 'schedule',
		'standings'    => 'standings',
		'racers'       => 'racers',
		'rules'        => 'rules',
		'classifieds'  => 'classifieds',
		'contact'      => 'contact',
		'tracks'       => 'tracks',
	);
	foreach ( $page_slug_map as $slug => $css ) {
		if ( is_page( $slug ) ) {
			$css_path = VMRA_THEME_DIR . '/assets/css/' . $css . '.css';
			if ( file_exists( $css_path ) ) {
				wp_enqueue_style(
					'vmra-page-' . $css,
					VMRA_THEME_URI . '/assets/css/' . $css . '.css',
					array( 'vmra-shell' ),
					VMRA_THEME_VERSION
				);
			}
		}
	}

	// 5. Shell JS — deferred so it runs after the DOM is ready.
	wp_enqueue_script(
		'vmra-shell',
		VMRA_THEME_URI . '/assets/js/shell.js',
		array(),
		VMRA_THEME_VERSION,
		array( 'strategy' => 'defer', 'in_footer' => false )
	);

	// 6. WordPress block styles (Gutenberg) — keeps wide/full blocks behaving
	//    inside content we don't control.
	wp_enqueue_style( 'wp-block-library' );
} );

/**
 * Preload the two heaviest fonts above the fold.
 * Same pattern as the static site's <link rel="preload"> tags.
 */
add_action( 'wp_head', function () {
	$fonts = array(
		'V8mDoQDjQSkFtoMM3T6r8E7mPbF4C_k3HqU.woff2',
		'1Ptgg87LROyAm3Kz-C8CSKlv.woff2',
	);
	foreach ( $fonts as $file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
			esc_url( VMRA_THEME_URI . '/assets/fonts/' . $file )
		);
	}
}, 1 );
