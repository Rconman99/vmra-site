<?php
/**
 * ACF field groups registered programmatically via acf_add_local_field_group().
 *
 * When ACF (or ACF Pro) is active, these field groups auto-attach to the
 * corresponding CPT edit screens. No import step needed.
 *
 * When ACF is NOT active, the functions no-op silently (acf_add_local_field_group
 * just isn't defined) — the theme still works, but these custom fields won't
 * appear on the edit screens.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'acf/init', function () {

	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// ---------------------------------------------------------------------
	// 1. Driver field group (attached to vmra_driver)
	// ---------------------------------------------------------------------
	acf_add_local_field_group( array(
		'key'      => 'group_vmra_driver',
		'title'    => __( 'Driver Details', 'vmra' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'vmra_driver' ) ) ),
		'position' => 'normal',
		'style'    => 'default',
		'fields'   => array(
			array( 'key' => 'field_driver_car_number',    'label' => 'Car Number',        'name' => 'car_number',    'type' => 'text',    'instructions' => 'e.g. 23, 23x, 25RT. Include suffix letters if applicable.', 'required' => 1, 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_driver_hometown',      'label' => 'Hometown',          'name' => 'hometown',      'type' => 'text',    'instructions' => 'e.g. "Monroe, WA". Shown on driver cards.',             'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_driver_is_rookie',     'label' => 'Rookie?',           'name' => 'is_rookie',     'type' => 'true_false', 'instructions' => 'Check if this driver is in their rookie year.', 'ui' => 1, 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_driver_rookie_year',   'label' => 'Rookie Year',       'name' => 'rookie_year',   'type' => 'number',  'instructions' => 'First full VMRA season, e.g. 2026.',                    'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_driver_championships', 'label' => 'Championships Won', 'name' => 'championships', 'type' => 'number',  'instructions' => 'Total VMRA titles.',                                    'wrapper' => array( 'width' => 25 ), 'default_value' => 0 ),
			array( 'key' => 'field_driver_defending',     'label' => 'Defending Champion?', 'name' => 'defending_champion', 'type' => 'true_false', 'ui' => 1, 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_driver_active',        'label' => 'Active in 2026?',   'name' => 'active_2026',   'type' => 'true_false', 'ui' => 1, 'default_value' => 1, 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_driver_car_photo',     'label' => 'Car Photo',         'name' => 'car_photo',     'type' => 'image',   'instructions' => 'Paddock shot (landscape). Used on the driver profile page.', 'return_format' => 'array' ),
		),
	) );

	// ---------------------------------------------------------------------
	// 2. Track field group (attached to vmra_track)
	// ---------------------------------------------------------------------
	acf_add_local_field_group( array(
		'key'      => 'group_vmra_track',
		'title'    => __( 'Track Details', 'vmra' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'vmra_track' ) ) ),
		'position' => 'normal',
		'fields'   => array(
			array( 'key' => 'field_track_city_state',  'label' => 'City, State',     'name' => 'city_state',  'type' => 'text', 'instructions' => 'e.g. "Monroe, WA".',     'wrapper' => array( 'width' => 50 ) ),
			array( 'key' => 'field_track_shape',       'label' => 'Shape',           'name' => 'shape',       'type' => 'text', 'instructions' => 'e.g. "Paved oval".',     'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_track_length',      'label' => 'Length',          'name' => 'length',      'type' => 'text', 'instructions' => 'e.g. "⅝ mile".',         'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_track_surface',     'label' => 'Surface',         'name' => 'surface',     'type' => 'text', 'instructions' => 'e.g. "Asphalt".',        'wrapper' => array( 'width' => 33 ) ),
			array( 'key' => 'field_track_address',     'label' => 'Street Address',  'name' => 'address',     'type' => 'text', 'instructions' => 'Used for Google Maps link.', 'wrapper' => array( 'width' => 34 ) ),
			array( 'key' => 'field_track_website',     'label' => 'Track Website',   'name' => 'website',     'type' => 'url',  'instructions' => 'Official site, optional.', 'wrapper' => array( 'width' => 33 ) ),
			array( 'key' => 'field_track_rounds_2026', 'label' => '2026 Rounds',     'name' => 'rounds_2026', 'type' => 'number', 'instructions' => 'How many VMRA rounds at this track in 2026.', 'default_value' => 0, 'wrapper' => array( 'width' => 25 ) ),
		),
	) );

	// ---------------------------------------------------------------------
	// 3. Race field group (attached to vmra_race) — this is the big one
	// ---------------------------------------------------------------------
	acf_add_local_field_group( array(
		'key'      => 'group_vmra_race',
		'title'    => __( 'Race Details', 'vmra' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'vmra_race' ) ) ),
		'position' => 'normal',
		'fields'   => array(
			array( 'key' => 'field_race_round',        'label' => 'Round Number',   'name' => 'round_number',  'type' => 'number',     'required' => 1, 'min' => 1, 'max' => 20, 'instructions' => '1 through 11 for the 2026 season.', 'wrapper' => array( 'width' => 20 ) ),
			array( 'key' => 'field_race_date',         'label' => 'Race Date',      'name' => 'race_date',     'type' => 'date_picker', 'required' => 1, 'return_format' => 'Y-m-d', 'display_format' => 'F j, Y', 'wrapper' => array( 'width' => 20 ) ),
			array( 'key' => 'field_race_track',        'label' => 'Track',          'name' => 'track',         'type' => 'post_object', 'post_type' => array( 'vmra_track' ), 'return_format' => 'object', 'wrapper' => array( 'width' => 30 ) ),
			array( 'key' => 'field_race_event_name',   'label' => 'Event Name',     'name' => 'event_name',    'type' => 'text',       'instructions' => 'e.g. "57th Apple Cup".', 'wrapper' => array( 'width' => 30 ) ),
			array( 'key' => 'field_race_is_points',    'label' => 'Points Round?',  'name' => 'is_points',     'type' => 'true_false', 'ui' => 1, 'default_value' => 1, 'instructions' => 'Uncheck for non-points specials.', 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_race_is_completed', 'label' => 'Race Completed?', 'name' => 'is_completed', 'type' => 'true_false', 'ui' => 1, 'instructions' => 'Check once the race has run.', 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_race_winner',       'label' => 'Winner',         'name' => 'winner',        'type' => 'post_object', 'post_type' => array( 'vmra_driver' ), 'return_format' => 'object', 'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_race_car_count',    'label' => 'Car Count',      'name' => 'car_count',     'type' => 'number',     'instructions' => 'Total entries.',             'wrapper' => array( 'width' => 25 ) ),
			array( 'key' => 'field_race_winner_note',  'label' => 'Winner Note',    'name' => 'winner_note',   'type' => 'textarea',   'rows' => 2, 'instructions' => 'One or two sentences of color from the win.' ),
			array(
				'key'        => 'field_race_results',
				'label'      => 'Results — Per Driver',
				'name'       => 'results_table',
				'type'       => 'repeater',
				'layout'     => 'table',
				'button_label' => 'Add Driver Result',
				'sub_fields' => array(
					array( 'key' => 'field_rr_driver',     'label' => 'Driver',     'name' => 'driver',     'type' => 'post_object', 'post_type' => array( 'vmra_driver' ), 'return_format' => 'id' ),
					array( 'key' => 'field_rr_qualify',    'label' => 'Qualify',    'name' => 'qualify_pts', 'type' => 'number', 'default_value' => 0 ),
					array( 'key' => 'field_rr_heat',       'label' => 'Heat',       'name' => 'heat_pts',    'type' => 'number', 'default_value' => 0 ),
					array( 'key' => 'field_rr_main',       'label' => 'Main',       'name' => 'main_pts',    'type' => 'number', 'default_value' => 0 ),
					array( 'key' => 'field_rr_showup',     'label' => 'Show-up',    'name' => 'showup_pts',  'type' => 'number', 'default_value' => 20 ),
					array( 'key' => 'field_rr_total',      'label' => 'Total',      'name' => 'total_on_date', 'type' => 'number', 'instructions' => 'Auto = Q+H+M+S.', 'default_value' => 0 ),
				),
			),
		),
	) );

	// ---------------------------------------------------------------------
	// 4. News field group (attached to vmra_news)
	// ---------------------------------------------------------------------
	acf_add_local_field_group( array(
		'key'      => 'group_vmra_news',
		'title'    => __( 'Article Details', 'vmra' ),
		'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'vmra_news' ) ) ),
		'position' => 'normal',
		'fields'   => array(
			array( 'key' => 'field_news_byline',       'label' => 'Byline',           'name' => 'byline',           'type' => 'text',        'instructions' => 'Author display name, e.g. "The VMRA Desk".', 'wrapper' => array( 'width' => 33 ) ),
			array( 'key' => 'field_news_is_feature',   'label' => 'Pin as Feature?',  'name' => 'is_feature',       'type' => 'true_false',  'ui' => 1, 'instructions' => 'Shows as the big card on the homepage.', 'wrapper' => array( 'width' => 33 ) ),
			array( 'key' => 'field_news_card_car_num', 'label' => 'Card Car Number',  'name' => 'card_car_number',  'type' => 'text',        'instructions' => 'Big number shown on the feature card (optional).', 'wrapper' => array( 'width' => 34 ) ),
			array( 'key' => 'field_news_related_race', 'label' => 'Related Race',     'name' => 'related_race',     'type' => 'post_object', 'post_type' => array( 'vmra_race' ), 'return_format' => 'object', 'instructions' => 'Link this article to a race recap (optional).' ),
		),
	) );
} );
