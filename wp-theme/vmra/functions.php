<?php
/**
 * VMRA 40th Anniversary theme · functions.php
 *
 * Entry point for theme setup, asset registration, custom post types,
 * and admin UX. Keep this file thin — move real logic into /inc/.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'VMRA_THEME_VERSION', '1.3.0' );
define( 'VMRA_THEME_DIR',     get_template_directory() );
define( 'VMRA_THEME_URI',     get_template_directory_uri() );

// ---------------------------------------------------------------------------
// 1. Theme setup — runs once after theme is loaded.
// ---------------------------------------------------------------------------
add_action( 'after_setup_theme', function () {

	// Let WordPress manage the <title> tag for us.
	add_theme_support( 'title-tag' );

	// Feature images on drivers, races, news items.
	add_theme_support( 'post-thumbnails' );
	add_image_size( 'vmra-card',  800,  520, true );   // 16:10 news card
	add_image_size( 'vmra-hero',  1920, 920, true );   // hero backgrounds
	add_image_size( 'vmra-plate', 400,  400, true );   // driver headshots

	// Output HTML5 for the built-ins (search, comments, gallery, captions).
	add_theme_support( 'html5', array(
		'search-form',
		'comment-form',
		'comment-list',
		'gallery',
		'caption',
		'style',
		'script',
	) );

	// Custom logo with the same sizing as the static shell.
	add_theme_support( 'custom-logo', array(
		'height'      => 40,
		'width'       => 40,
		'flex-height' => true,
		'flex-width'  => true,
	) );

	// Register two WP nav menus: the top nav and the mobile menu
	// (we render them from the same menu slug by default).
	register_nav_menus( array(
		'primary'        => __( 'Primary Nav (desktop + mobile)', 'vmra' ),
		'footer-racing'  => __( 'Footer · Racing column',        'vmra' ),
		'footer-members' => __( 'Footer · Members column',       'vmra' ),
		'footer-connect' => __( 'Footer · Connect column',       'vmra' ),
	) );

	// Feed links <link rel="alternate"> in <head>.
	add_theme_support( 'automatic-feed-links' );

	// Wide + full-width Gutenberg alignments inside content.
	add_theme_support( 'align-wide' );

	// Load translation files from /languages/ if they exist.
	load_theme_textdomain( 'vmra', VMRA_THEME_DIR . '/languages' );
} );

// ---------------------------------------------------------------------------
// 2. Load the rest of the theme from /inc/ — keeps this file readable.
// ---------------------------------------------------------------------------
require_once VMRA_THEME_DIR . '/inc/enqueue.php';      // CSS/JS registration
require_once VMRA_THEME_DIR . '/inc/cpt-driver.php';   // Driver CPT
require_once VMRA_THEME_DIR . '/inc/cpt-race.php';     // Race CPT
require_once VMRA_THEME_DIR . '/inc/cpt-track.php';    // Track CPT
require_once VMRA_THEME_DIR . '/inc/cpt-news.php';     // News CPT
require_once VMRA_THEME_DIR . '/inc/admin-notices.php'; // Plugin-dependency warnings
require_once VMRA_THEME_DIR . '/inc/acf-field-groups.php'; // ACF field groups (no-op if ACF not active)
require_once VMRA_THEME_DIR . '/inc/race-update-admin.php'; // Board's weekly race-update parser page
require_once VMRA_THEME_DIR . '/inc/migrate-seed-data.php'; // One-click CPT seed from data/*.json

// ---------------------------------------------------------------------------
// 3. Helpers — small utility functions used by templates.
// ---------------------------------------------------------------------------

/**
 * Load a template part from /templates/ with optional variables.
 * Keeps template files readable by extracting repeated blocks (banner, nav, etc.).
 *
 * @param string $slug Template file basename without extension.
 * @param array  $args Variables to extract into the partial's scope.
 */
function vmra_template( $slug, $args = array() ) {
	$path = VMRA_THEME_DIR . '/templates/' . $slug . '.php';
	if ( ! file_exists( $path ) ) {
		return;
	}
	if ( is_array( $args ) ) {
		extract( $args, EXTR_SKIP );
	}
	include $path;
}

/**
 * Read the seed JSON that ships with the theme (data/schedule.json etc).
 * Useful during migration — templates can fall back to the seed if the WP
 * database hasn't been populated yet.
 *
 * @param string $name One of: schedule, standings, results, news.
 * @return array|null  Parsed JSON as associative array, or null on failure.
 */
function vmra_seed_data( $name ) {
	static $cache = array();
	if ( isset( $cache[ $name ] ) ) {
		return $cache[ $name ];
	}
	$file = VMRA_THEME_DIR . '/data/' . $name . '.json';
	if ( ! file_exists( $file ) ) {
		return null;
	}
	$raw = file_get_contents( $file );
	$cache[ $name ] = json_decode( $raw, true );
	return $cache[ $name ];
}
