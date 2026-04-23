<?php
/**
 * Custom Post Type: Track
 *
 * One post per sanctioned oval on the VMRA tour. Races link to tracks
 * via a post-object relationship field so we don't repeat track info.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	$labels = array(
		'name'               => __( 'Tracks',              'vmra' ),
		'singular_name'      => __( 'Track',               'vmra' ),
		'add_new'            => __( 'Add Track',           'vmra' ),
		'add_new_item'       => __( 'Add New Track',       'vmra' ),
		'edit_item'          => __( 'Edit Track',          'vmra' ),
		'new_item'           => __( 'New Track',           'vmra' ),
		'view_item'          => __( 'View Track',          'vmra' ),
		'search_items'       => __( 'Search Tracks',       'vmra' ),
		'not_found'          => __( 'No tracks found.',    'vmra' ),
		'not_found_in_trash' => __( 'No tracks in trash.', 'vmra' ),
		'menu_name'          => __( 'Tracks',              'vmra' ),
	);

	register_post_type( 'vmra_track', array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'menu_position'       => 23,
		'menu_icon'           => 'dashicons-location-alt',
		// has_archive off: /tracks/ is a Page. Single-track permalink: /track/{slug}.
		'has_archive'         => false,
		'rewrite'             => array( 'slug' => 'track', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'thumbnail' ),
		'hierarchical'        => false,
	) );
} );

/**
 * Required ACF field group:
 *
 * city_state   (text)   "West Richland, WA"
 * shape        (text)   "Paved oval"
 * length       (text)   "⅝ mile"
 * surface      (text)   "Asphalt"
 * address      (text)   Street address
 * website      (url)    Official track URL
 * rounds_2026  (number) How many VMRA rounds here in 2026
 *
 * See docs/acf-track-fields.json for the importable JSON.
 */
