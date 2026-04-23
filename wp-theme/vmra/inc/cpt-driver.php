<?php
/**
 * Custom Post Type: Driver
 *
 * One WP post per VMRA driver. Stores the permanent info (name, hometown,
 * car number, photos). Per-race points are not stored here — they live on
 * the Race CPT as a repeater field so history is preserved.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	$labels = array(
		'name'               => __( 'Drivers',            'vmra' ),
		'singular_name'      => __( 'Driver',             'vmra' ),
		'add_new'            => __( 'Add Driver',         'vmra' ),
		'add_new_item'       => __( 'Add New Driver',     'vmra' ),
		'edit_item'          => __( 'Edit Driver',        'vmra' ),
		'new_item'           => __( 'New Driver',         'vmra' ),
		'view_item'          => __( 'View Driver',        'vmra' ),
		'search_items'       => __( 'Search Drivers',     'vmra' ),
		'not_found'          => __( 'No drivers found.',  'vmra' ),
		'not_found_in_trash' => __( 'No drivers in trash.', 'vmra' ),
		'menu_name'          => __( 'Drivers',            'vmra' ),
	);

	register_post_type( 'vmra_driver', array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,   // REST API + block editor
		'menu_position'       => 21,
		'menu_icon'           => 'dashicons-groups',
		// has_archive off: /racers/ is a Page. Single-driver permalink: /racers/{slug}.
		'has_archive'         => false,
		'rewrite'             => array( 'slug' => 'driver', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'hierarchical'        => false,
	) );
} );

/**
 * Required ACF field group (importable once ACF Free is installed).
 *
 * car_number   (text)   e.g. "23", "23x", "25RT"
 * hometown     (text)   e.g. "Monroe, WA"
 * is_rookie    (bool)   Currently a rookie?
 * rookie_year  (number) First full VMRA season
 * championships (number) Total titles won
 * defending_champ (bool) Holds the current season title
 * car_photo    (image)  Paddock shot
 * active       (bool)   On the 2026 grid?
 *
 * See docs/acf-driver-fields.json for the importable JSON.
 */
