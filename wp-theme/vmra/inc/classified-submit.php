<?php
/**
 * Classified submission REST endpoint.
 *
 *   POST /wp-json/vmra/v1/classified   (multipart/form-data)
 *
 * Anti-abuse layers (we auto-publish, so the floor is load-bearing):
 *   1. Honeypot "website" field — invisible to humans, bots fill it.
 *   2. Per-IP rate limit — 2 submissions / hour, transient-based.
 *   3. Content validation — length bounds, required fields, email format,
 *      category whitelist.
 *   4. Description sanitizing — allow only <br> and <p>; strip URLs to
 *      deny SEO backlink spam while keeping seller_email/phone readable.
 *   5. Photo policy — JPEG / PNG / WebP only, 5MB each, max 3 total.
 *
 * On success: returns 201 with { ok, id, permalink } and notifies the board.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'rest_api_init', function () {
	register_rest_route( 'vmra/v1', '/classified', array(
		'methods'             => 'POST',
		'callback'            => 'vmra_classified_submit',
		'permission_callback' => '__return_true',
	) );
} );

function vmra_classified_submit( WP_REST_Request $req ) {
	// 1. Honeypot — any non-empty value here means it's a bot.
	if ( trim( (string) $req->get_param( 'website' ) ) !== '' ) {
		return new WP_Error( 'spam', 'Submission rejected.', array( 'status' => 422 ) );
	}

	// 2. Per-IP rate limit (2 per hour).
	$ip    = vmra_classified_client_ip();
	$rlkey = 'vmra_cls_rl_' . md5( $ip );
	$count = (int) get_transient( $rlkey );
	if ( $count >= 2 ) {
		return new WP_Error( 'rate_limited',
			'Too many submissions from this network. Please wait an hour and try again.',
			array( 'status' => 429 )
		);
	}

	// 3. Pull + sanitize inputs.
	$title        = sanitize_text_field( (string) $req->get_param( 'title' ) );
	$description  = (string) $req->get_param( 'description' );
	$price        = sanitize_text_field( (string) $req->get_param( 'price' ) );
	$category     = sanitize_key( (string) $req->get_param( 'category' ) );
	$condition    = sanitize_key( (string) $req->get_param( 'condition' ) );
	$location     = sanitize_text_field( (string) $req->get_param( 'location' ) );
	$seller_name  = sanitize_text_field( (string) $req->get_param( 'seller_name' ) );
	$seller_email = sanitize_email( (string) $req->get_param( 'seller_email' ) );
	$seller_phone = sanitize_text_field( (string) $req->get_param( 'seller_phone' ) );
	$show_email   = filter_var( $req->get_param( 'show_email' ), FILTER_VALIDATE_BOOLEAN );
	$show_phone   = filter_var( $req->get_param( 'show_phone' ), FILTER_VALIDATE_BOOLEAN );

	// 3b. Validation bundle — collect all errors for one useful response.
	$errors = array();
	$title_len = mb_strlen( $title );
	if ( $title_len < 5 || $title_len > 80 ) {
		$errors[] = 'Title must be 5-80 characters.';
	}
	$desc_stripped = wp_strip_all_tags( $description );
	if ( mb_strlen( $desc_stripped ) < 20 ) {
		$errors[] = 'Description must be at least 20 characters.';
	}
	if ( mb_strlen( $description ) > 3000 ) {
		$errors[] = 'Description too long (max 3000 characters).';
	}
	if ( $price === '' ) {
		$errors[] = 'Price is required. Use "Trade" or "OBO" if flexible.';
	}
	$allowed_cats = array( 'cars', 'engines', 'parts', 'trailers', 'tools', 'wanted' );
	if ( ! in_array( $category, $allowed_cats, true ) ) {
		$errors[] = 'Please pick a category.';
	}
	if ( $seller_name === '' ) {
		$errors[] = 'Seller name is required.';
	}
	if ( $seller_email === '' || ! is_email( $seller_email ) ) {
		$errors[] = 'Valid seller email is required (used for SOLD confirmation, not shown publicly unless you toggle it on).';
	}
	if ( $show_phone && $seller_phone === '' ) {
		$errors[] = 'You chose to show your phone publicly — please provide a phone number.';
	}

	if ( ! empty( $errors ) ) {
		return new WP_Error( 'validation', implode( ' ', $errors ),
			array( 'status' => 400, 'fields' => $errors ) );
	}

	// 4. Sanitize the description — allow only <br> and <p>, strip URLs.
	$description = wp_kses( $description, array(
		'br' => array(),
		'p'  => array(),
	) );
	$description = preg_replace( '#https?://\S+#i', '[link removed]', $description );
	$description = preg_replace( '#\b\w+\.(com|net|org|io|biz|info|co|us|app)\S*#i', '[link removed]', $description );
	$description = trim( $description );

	// 5. Create the post (auto-publish).
	$post_id = wp_insert_post( array(
		'post_type'    => 'vmra_classified',
		'post_status'  => 'publish',
		'post_title'   => $title,
		'post_content' => $description,
		'post_author'  => 0,
	), true );

	if ( is_wp_error( $post_id ) ) {
		return $post_id;
	}

	// Category term
	wp_set_object_terms( $post_id, $category, 'vmra_classified_cat' );

	// Post meta (works with or without ACF active)
	update_post_meta( $post_id, 'price',        $price );
	update_post_meta( $post_id, 'condition',    $condition );
	update_post_meta( $post_id, 'location',     $location );
	update_post_meta( $post_id, 'seller_name',  $seller_name );
	update_post_meta( $post_id, 'seller_email', $seller_email );
	update_post_meta( $post_id, 'seller_phone', $seller_phone );
	update_post_meta( $post_id, 'show_email',   $show_email ? 1 : 0 );
	update_post_meta( $post_id, 'show_phone',   $show_phone ? 1 : 0 );
	update_post_meta( $post_id, 'submit_ip',    $ip );
	update_post_meta( $post_id, 'submit_ts',    gmdate( 'c' ) );

	// 6. Photos (optional, up to 3). Don't fail the whole submission if upload breaks.
	$photo_ids = vmra_classified_handle_photos( $req, $post_id );
	if ( is_wp_error( $photo_ids ) ) {
		error_log( '[vmra_classified] photo upload failed: ' . $photo_ids->get_error_message() );
		$photo_ids = array();
	}
	if ( ! empty( $photo_ids ) ) {
		set_post_thumbnail( $post_id, $photo_ids[0] );
		if ( count( $photo_ids ) > 1 ) {
			update_post_meta( $post_id, 'photos', array_slice( $photo_ids, 1 ) );
		}
	}

	// 7. Rate limit increment
	set_transient( $rlkey, $count + 1, HOUR_IN_SECONDS );

	// 8. Notify board (fire-and-forget; don't block response).
	$body  = "A new classified listing just went live:\n\n";
	$body .= get_permalink( $post_id ) . "\n\n";
	$body .= "Seller: {$seller_name} <{$seller_email}>";
	if ( $seller_phone ) { $body .= " · {$seller_phone}"; }
	$body .= "\nShow email public: " . ( $show_email ? 'YES' : 'no' );
	$body .= "\nShow phone public: " . ( $show_phone ? 'YES' : 'no' );
	$body .= "\nCategory: {$category}";
	$body .= "\nPrice: {$price}";
	$body .= "\nLocation: {$location}";
	$body .= "\nIP: {$ip}\n\n";
	$body .= "When it sells, the seller will email you SOLD and you flip the badge in /wp-admin/.";
	$body .= "\nIf it violates rules, delete it from /wp-admin/edit.php?post_type=vmra_classified.";

	wp_mail( 'board@vmra.club', '[VMRA Classifieds] New listing: ' . $title, $body );

	return new WP_REST_Response( array(
		'ok'        => true,
		'id'        => $post_id,
		'permalink' => get_permalink( $post_id ),
	), 201 );
}

/**
 * Best-effort client IP extractor. Trusts CF/proxy headers when present,
 * falls back to REMOTE_ADDR. Used only for rate limiting + audit log.
 */
function vmra_classified_client_ip() {
	$keys = array( 'HTTP_CF_CONNECTING_IP', 'HTTP_X_REAL_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR' );
	foreach ( $keys as $k ) {
		if ( ! empty( $_SERVER[ $k ] ) ) {
			$ip = trim( explode( ',', (string) $_SERVER[ $k ] )[0] );
			if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
				return $ip;
			}
		}
	}
	return '0.0.0.0';
}

/**
 * Validate + ingest up to 3 photo uploads into the WP media library,
 * attached to the newly-created classified post.
 *
 * Fields in the multipart request: photo_0, photo_1, photo_2.
 * MIME allow-list: image/jpeg, image/png, image/webp.
 * Size cap: 5 MB per file.
 *
 * @return int[] attachment IDs in upload order (may be empty)
 */
function vmra_classified_handle_photos( WP_REST_Request $req, $post_id ) {
	$files = $req->get_file_params();
	if ( empty( $files ) ) {
		return array();
	}

	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$allowed_mimes = array( 'image/jpeg', 'image/png', 'image/webp' );
	$max_size      = 5 * 1024 * 1024;
	$max_count     = 3;

	$uploaded = array();
	$count    = 0;

	foreach ( $files as $field_name => $file ) {
		if ( $count >= $max_count ) break;

		// Basic upload sanity
		if ( empty( $file['tmp_name'] ) || ! is_uploaded_file( $file['tmp_name'] ) ) continue;
		if ( ! empty( $file['error'] ) )                                              continue;
		if ( $file['size'] > $max_size )                                              continue;

		// MIME + ext check
		$type_info = wp_check_filetype_and_ext( $file['tmp_name'], $file['name'] );
		if ( empty( $type_info['type'] ) || ! in_array( $type_info['type'], $allowed_mimes, true ) ) {
			continue;
		}

		// media_handle_upload reads from the global $_FILES — rebuild for this one field
		$_FILES = array( $field_name => $file );
		$attachment_id = media_handle_upload( $field_name, $post_id );
		if ( is_wp_error( $attachment_id ) ) continue;

		$uploaded[] = (int) $attachment_id;
		$count++;
	}

	return $uploaded;
}
