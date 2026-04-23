<?php
/**
 * Custom Post Type: Race
 *
 * One WP post per race on the calendar (round 1 through 11). Holds the
 * date, track, event name, and — after the race — results and winner.
 *
 * Points awarded in each race live on the race post as a repeater (via ACF),
 * so historical points are preserved and season standings are computed by
 * summing across races rather than stored on the Driver CPT.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	$labels = array(
		'name'               => __( 'Races',              'vmra' ),
		'singular_name'      => __( 'Race',               'vmra' ),
		'add_new'            => __( 'Add Race',           'vmra' ),
		'add_new_item'       => __( 'Add New Race',       'vmra' ),
		'edit_item'          => __( 'Edit Race',          'vmra' ),
		'new_item'           => __( 'New Race',           'vmra' ),
		'view_item'          => __( 'View Race',          'vmra' ),
		'search_items'       => __( 'Search Races',       'vmra' ),
		'not_found'          => __( 'No races found.',    'vmra' ),
		'not_found_in_trash' => __( 'No races in trash.', 'vmra' ),
		'menu_name'          => __( 'Races',              'vmra' ),
	);

	register_post_type( 'vmra_race', array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'menu_position'       => 22,
		'menu_icon'           => 'dashicons-flag',
		// has_archive intentionally false: /schedule/ is a Page that lists races.
		// Single race permalinks stay at /races/{slug}.
		'has_archive'         => false,
		'rewrite'             => array( 'slug' => 'races', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt' ),
		'hierarchical'        => false,
	) );
} );

/**
 * Required ACF field group:
 *
 * round_number   (number)      1–11
 * race_date      (date picker) e.g. 2026-04-12
 * track          (post object, filtered to vmra_track)
 * event_name     (text)        e.g. "57th Apple Cup"
 * is_points      (bool)        Points round? (false for R05 and R11 non-points)
 * is_completed   (bool)        Has this race run?
 * winner         (post object, filtered to vmra_driver)
 * winner_note    (textarea)    1–2 sentence color
 * car_count      (number)      Cars on the entry list (optional)
 * results_table  (repeater)
 *   └ driver         (post object, filtered to vmra_driver)
 *   └ qualify_pts    (number)
 *   └ heat_pts       (number)
 *   └ main_pts       (number)
 *   └ showup_pts     (number, default 20)
 *   └ total_on_date  (number, computed)
 *
 * See docs/acf-race-fields.json for the importable JSON.
 */
