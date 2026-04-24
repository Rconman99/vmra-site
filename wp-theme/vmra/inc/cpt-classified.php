<?php
/**
 * Custom Post Type: Classified
 *
 * VMRA members-and-friends classifieds board. Sellers submit via the public
 * form on /classifieds/; listings auto-publish (anti-abuse is enforced in the
 * REST endpoint — see inc/classified-submit.php).
 *
 * Data layout:
 *   post_title    — listing title
 *   post_content  — description (anchor tags stripped, bare URLs removed)
 *   thumbnail     — primary photo (first upload)
 *   post meta:
 *     price, condition, location
 *     seller_name, seller_email, seller_phone
 *     show_email (0|1), show_phone (0|1)
 *     photos (array of attachment IDs, extras beyond thumbnail)
 *     sold_at (board-set)
 *     submit_ip, submit_ts (audit)
 *
 * Taxonomy: vmra_classified_cat (cars, engines, parts, trailers, tools, wanted).
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	$labels = array(
		'name'               => __( 'Classifieds', 'vmra' ),
		'singular_name'      => __( 'Classified', 'vmra' ),
		'add_new'            => __( 'Add Listing', 'vmra' ),
		'add_new_item'       => __( 'Add New Listing', 'vmra' ),
		'edit_item'          => __( 'Edit Listing', 'vmra' ),
		'new_item'           => __( 'New Listing', 'vmra' ),
		'view_item'          => __( 'View Listing', 'vmra' ),
		'search_items'       => __( 'Search Listings', 'vmra' ),
		'not_found'          => __( 'No listings found.', 'vmra' ),
		'not_found_in_trash' => __( 'No listings in trash.', 'vmra' ),
		'menu_name'          => __( 'Classifieds', 'vmra' ),
	);

	register_post_type( 'vmra_classified', array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'menu_position'       => 25,
		'menu_icon'           => 'dashicons-cart',
		'has_archive'         => false,
		'rewrite'             => array( 'slug' => 'classified', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		'hierarchical'        => false,
	) );

	// Taxonomy: category (cars, engines, parts, trailers, tools, wanted)
	register_taxonomy( 'vmra_classified_cat', 'vmra_classified', array(
		'labels' => array(
			'name'          => __( 'Categories',  'vmra' ),
			'singular_name' => __( 'Category',    'vmra' ),
		),
		'hierarchical'      => true,
		'show_ui'           => true,
		'show_admin_column' => true,
		'show_in_rest'      => true,
		'rewrite'           => array( 'slug' => 'classified-category' ),
	) );

	// Seed the 6 categories on first init. Idempotent.
	$seed = array(
		'cars'     => 'Race Cars',
		'engines'  => 'Engines',
		'parts'    => 'Parts',
		'trailers' => 'Trailers',
		'tools'    => 'Tools / Shop',
		'wanted'   => 'Wanted',
	);
	foreach ( $seed as $slug => $name ) {
		if ( ! term_exists( $slug, 'vmra_classified_cat' ) ) {
			wp_insert_term( $name, 'vmra_classified_cat', array( 'slug' => $slug ) );
		}
	}
} );

/**
 * Register ACF field group programmatically so board admins get a nice UI
 * without importing JSON. Gracefully no-ops when ACF isn't installed — the
 * REST endpoint still writes post_meta, and admins can edit via the native
 * Custom Fields meta box.
 */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	acf_add_local_field_group( array(
		'key'      => 'group_vmra_classified',
		'title'    => 'Classified · Listing Details',
		'location' => array( array( array(
			'param'    => 'post_type',
			'operator' => '==',
			'value'    => 'vmra_classified',
		) ) ),
		'fields'   => array(
			array( 'key' => 'f_cls_price', 'name' => 'price', 'label' => 'Price', 'type' => 'text',
				'instructions' => 'e.g. "$850", "$1,200 OBO", or "Trade"' ),
			array( 'key' => 'f_cls_condition', 'name' => 'condition', 'label' => 'Condition', 'type' => 'select',
				'choices' => array(
					''         => '—',
					'new'      => 'New',
					'like-new' => 'Used · like new',
					'good'     => 'Used · good',
					'fair'     => 'Used · fair',
					'parts'    => 'For parts',
				) ),
			array( 'key' => 'f_cls_location', 'name' => 'location', 'label' => 'Location', 'type' => 'text',
				'instructions' => 'City, State (e.g. Monroe, WA)' ),
			array( 'key' => 'f_cls_seller_name',  'name' => 'seller_name',  'label' => 'Seller name',  'type' => 'text' ),
			array( 'key' => 'f_cls_seller_email', 'name' => 'seller_email', 'label' => 'Seller email', 'type' => 'email' ),
			array( 'key' => 'f_cls_seller_phone', 'name' => 'seller_phone', 'label' => 'Seller phone', 'type' => 'text' ),
			array( 'key' => 'f_cls_show_email', 'name' => 'show_email', 'label' => 'Show email publicly?',
				'type' => 'true_false', 'default_value' => 0, 'ui' => 1 ),
			array( 'key' => 'f_cls_show_phone', 'name' => 'show_phone', 'label' => 'Show phone publicly?',
				'type' => 'true_false', 'default_value' => 0, 'ui' => 1 ),
			array( 'key' => 'f_cls_photos', 'name' => 'photos', 'label' => 'Additional photos',
				'type' => 'gallery', 'min' => 0, 'max' => 2,
				'instructions' => 'Primary photo = featured image above. Up to 2 extras here.' ),
			array( 'key' => 'f_cls_sold_at', 'name' => 'sold_at', 'label' => 'Sold date',
				'type' => 'date_picker', 'display_format' => 'Y-m-d',
				'instructions' => 'Board-only. Sets the SOLD badge on the listing.' ),
			array( 'key' => 'f_cls_submit_ip', 'name' => 'submit_ip', 'label' => 'Submission IP',
				'type' => 'text', 'readonly' => 1 ),
			array( 'key' => 'f_cls_submit_ts', 'name' => 'submit_ts', 'label' => 'Submitted at',
				'type' => 'text', 'readonly' => 1 ),
		),
	) );
} );

/**
 * Helpers used by page-classifieds.php and single-vmra_classified.php
 * to read the current listing's meta with sensible defaults.
 *
 * @param int|WP_Post $post
 * @return array
 */
function vmra_classified_meta( $post = null ) {
	$p = get_post( $post );
	if ( ! $p ) return array();
	$id = $p->ID;

	$category = '';
	$terms = get_the_terms( $id, 'vmra_classified_cat' );
	if ( is_array( $terms ) && ! empty( $terms ) ) {
		$category = $terms[0]->slug;
	}

	$photos = get_post_meta( $id, 'photos', true );
	if ( ! is_array( $photos ) ) $photos = array();

	return array(
		'id'            => $id,
		'title'         => get_the_title( $id ),
		'permalink'     => get_permalink( $id ),
		'description'   => apply_filters( 'the_content', $p->post_content ),
		'category'      => $category,
		'price'         => (string) get_post_meta( $id, 'price', true ),
		'condition'     => (string) get_post_meta( $id, 'condition', true ),
		'location'      => (string) get_post_meta( $id, 'location', true ),
		'seller_name'   => (string) get_post_meta( $id, 'seller_name', true ),
		'seller_email'  => (string) get_post_meta( $id, 'seller_email', true ),
		'seller_phone'  => (string) get_post_meta( $id, 'seller_phone', true ),
		'show_email'    => (int) get_post_meta( $id, 'show_email', true ) === 1,
		'show_phone'    => (int) get_post_meta( $id, 'show_phone', true ) === 1,
		'sold_at'       => (string) get_post_meta( $id, 'sold_at', true ),
		'primary_photo' => get_the_post_thumbnail_url( $id, 'vmra-card' ),
		'extra_photos'  => array_values( array_filter( array_map( function ( $aid ) {
			return wp_get_attachment_image_url( (int) $aid, 'large' );
		}, $photos ) ) ),
		'posted_human'  => human_time_diff( get_post_timestamp( $id ), current_time( 'timestamp' ) ),
	);
}
