<?php
/**
 * Custom Post Type: News
 *
 * Race recaps, previews, sponsor news, and rule updates. Mirrors the
 * items in the static site's /data/news.json.
 *
 * We use a custom post type (not WP's built-in posts) so the admin UI
 * presents "News" clearly in the sidebar and we can attach news-specific
 * ACF fields (category, byline, related race) without polluting built-in
 * posts.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'init', function () {
	$labels = array(
		'name'               => __( 'News',                 'vmra' ),
		'singular_name'      => __( 'News Article',         'vmra' ),
		'add_new'            => __( 'Add Article',          'vmra' ),
		'add_new_item'       => __( 'Add News Article',     'vmra' ),
		'edit_item'          => __( 'Edit News Article',    'vmra' ),
		'new_item'           => __( 'New News Article',     'vmra' ),
		'view_item'          => __( 'View News Article',    'vmra' ),
		'search_items'       => __( 'Search News',          'vmra' ),
		'not_found'          => __( 'No articles found.',   'vmra' ),
		'not_found_in_trash' => __( 'No articles in trash.', 'vmra' ),
		'menu_name'          => __( 'News',                 'vmra' ),
	);

	register_post_type( 'vmra_news', array(
		'labels'              => $labels,
		'public'              => true,
		'publicly_queryable'  => true,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'show_in_rest'        => true,
		'menu_position'       => 20,
		'menu_icon'           => 'dashicons-megaphone',
		// has_archive off: /news/ is a Page. Single-article permalink: /news/{slug}.
		'has_archive'         => false,
		'rewrite'             => array( 'slug' => 'news', 'with_front' => false ),
		'supports'            => array( 'title', 'editor', 'thumbnail', 'excerpt', 'author' ),
		'hierarchical'        => false,
	) );

	// A light taxonomy so the news index can be filtered by category.
	register_taxonomy( 'vmra_news_category', 'vmra_news', array(
		'labels'            => array(
			'name'          => __( 'News Categories', 'vmra' ),
			'singular_name' => __( 'News Category',   'vmra' ),
		),
		'hierarchical'      => true,
		'public'            => true,
		'show_in_rest'      => true,
		'show_admin_column' => true,
		'rewrite'           => array( 'slug' => 'news-category' ),
	) );
} );

/**
 * Required ACF field group:
 *
 * byline          (text)     Author display name (e.g. "The VMRA Desk")
 * related_race    (post object, filtered to vmra_race — optional)
 * is_feature      (bool)     Pin to top of homepage news grid
 * card_car_number (text)     Optional: big car number shown on the feature card
 *
 * See docs/acf-news-fields.json for the importable JSON.
 */
