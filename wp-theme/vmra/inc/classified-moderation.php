<?php
/**
 * Classifieds moderation — admin UX for approving/denying pending listings.
 *
 * Submissions land as post_status=pending via /wp-json/vmra/v1/classified.
 * The board approves by publishing (standard WP Publish button) or denies
 * via the "Deny & Notify Submitter" meta box on the edit screen.
 *
 *   Approve → transition pending → publish → auto-email seller "it's live"
 *   Deny    → trash post + save denial reason meta + email seller with reason
 *
 * All emails use wp_mail(); the From address inherits WP defaults unless a
 * plugin overrides it. Board contact: vmrainfo@gmail.com.
 *
 * @package vmra
 * @since   1.4.1
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// 1. Pending-count bubble on the admin menu — quick visual cue that something
//    is waiting for review. Shows "Classifieds (3)" in the sidebar.
// ---------------------------------------------------------------------------
add_filter( 'add_menu_classes', 'vmra_classified_pending_bubble' );
function vmra_classified_pending_bubble( $menu ) {
	$pending = wp_count_posts( 'vmra_classified' );
	$count   = isset( $pending->pending ) ? (int) $pending->pending : 0;
	if ( $count < 1 ) {
		return $menu;
	}
	foreach ( $menu as $k => $item ) {
		if ( isset( $item[2] ) && $item[2] === 'edit.php?post_type=vmra_classified' ) {
			$menu[ $k ][0] .= sprintf(
				' <span class="awaiting-mod count-%d"><span class="pending-count">%d</span></span>',
				$count,
				$count
			);
			break;
		}
	}
	return $menu;
}


// ---------------------------------------------------------------------------
// 2. Deny & Notify meta box — only shown on pending classifieds. Lets the
//    board reject a listing with a reason that's emailed to the submitter.
// ---------------------------------------------------------------------------
add_action( 'add_meta_boxes_vmra_classified', 'vmra_classified_add_deny_metabox' );
function vmra_classified_add_deny_metabox( $post ) {
	if ( $post->post_status !== 'pending' ) {
		return;
	}
	add_meta_box(
		'vmra_classified_deny',
		'Deny & Notify Submitter',
		'vmra_classified_deny_metabox_render',
		'vmra_classified',
		'side',
		'high'
	);
}

function vmra_classified_deny_metabox_render( $post ) {
	wp_nonce_field( 'vmra_classified_deny_' . $post->ID, 'vmra_classified_deny_nonce' );
	$seller_email = (string) get_post_meta( $post->ID, 'seller_email', true );
	$seller_name  = (string) get_post_meta( $post->ID, 'seller_name', true );
	?>
	<p style="font-size:12px;color:#646970;margin:4px 0 8px">
		Type a short reason. The submitter
		<?php echo $seller_name ? '<strong>(' . esc_html( $seller_name ) . ')</strong>' : ''; ?>
		gets it emailed to
		<strong><?php echo esc_html( $seller_email ?: 'their address' ); ?></strong>,
		this listing moves to Trash.
	</p>
	<textarea
		name="vmra_classified_deny_reason"
		rows="4"
		style="width:100%;font-family:monospace;font-size:12px"
		placeholder="E.g. Item is outside the vintage-modified scope. Or: listing looks like spam. Or: please resubmit with a clearer photo."
	></textarea>
	<p>
		<button
			type="submit"
			name="vmra_classified_deny_action"
			value="1"
			class="button button-secondary"
			style="background:#d11a2a;color:#fff;border-color:#a01521;width:100%;margin-top:6px"
			onclick="return confirm('Deny this listing and email the reason to the submitter?');"
		>Deny &amp; Notify Submitter</button>
	</p>
	<p style="font-size:11px;color:#646970;margin:8px 0 0">
		To <em>approve</em> instead, just hit the blue <strong>Publish</strong> button —
		the submitter gets an auto-email when it goes live.
	</p>
	<?php
}


// ---------------------------------------------------------------------------
// 3. Handle the deny submit. Runs on save_post (before post_updated hooks).
// ---------------------------------------------------------------------------
add_action( 'save_post_vmra_classified', 'vmra_classified_maybe_deny', 5, 2 );
function vmra_classified_maybe_deny( $post_id, $post ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}
	if ( empty( $_POST['vmra_classified_deny_action'] ) ) {
		return;
	}
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	if ( empty( $_POST['vmra_classified_deny_nonce'] ) ||
	     ! wp_verify_nonce( $_POST['vmra_classified_deny_nonce'], 'vmra_classified_deny_' . $post_id ) ) {
		return;
	}

	$reason = trim( sanitize_textarea_field( (string) ( $_POST['vmra_classified_deny_reason'] ?? '' ) ) );
	if ( $reason === '' ) {
		$reason = 'Your listing did not meet the VMRA Classifieds guidelines.';
	}

	update_post_meta( $post_id, 'denial_reason', $reason );
	update_post_meta( $post_id, 'denied_at',     gmdate( 'c' ) );
	update_post_meta( $post_id, 'denied_by',     get_current_user_id() );

	vmra_classified_email_submitter_denial( $post, $reason );

	// Move to Trash so it's out of the way but recoverable.
	remove_action( 'save_post_vmra_classified', 'vmra_classified_maybe_deny', 5 );
	wp_trash_post( $post_id );
	add_action( 'save_post_vmra_classified', 'vmra_classified_maybe_deny', 5, 2 );

	// Redirect back to the pending queue with a notice.
	wp_safe_redirect( add_query_arg(
		array( 'post_type' => 'vmra_classified', 'post_status' => 'pending', 'vmra_denied' => 1 ),
		admin_url( 'edit.php' )
	) );
	exit;
}


// ---------------------------------------------------------------------------
// 4. Admin notice — "Listing denied and submitter notified." after redirect.
// ---------------------------------------------------------------------------
add_action( 'admin_notices', 'vmra_classified_deny_admin_notice' );
function vmra_classified_deny_admin_notice() {
	if ( empty( $_GET['vmra_denied'] ) ) {
		return;
	}
	?>
	<div class="notice notice-success is-dismissible">
		<p><strong>Listing denied.</strong> The submitter has been notified with your reason, and the listing is in Trash.</p>
	</div>
	<?php
}


// ---------------------------------------------------------------------------
// 5. Auto-email on approval (pending → publish).
// ---------------------------------------------------------------------------
add_action( 'transition_post_status', 'vmra_classified_on_transition', 10, 3 );
function vmra_classified_on_transition( $new, $old, $post ) {
	if ( $post->post_type !== 'vmra_classified' ) {
		return;
	}
	if ( $new === 'publish' && $old === 'pending' ) {
		vmra_classified_email_submitter_approval( $post );
	}
}


// ---------------------------------------------------------------------------
// 6. Email templates — kept in this file for easy board review / edits.
// ---------------------------------------------------------------------------
function vmra_classified_email_submitter_approval( $post ) {
	$to   = (string) get_post_meta( $post->ID, 'seller_email', true );
	$name = (string) get_post_meta( $post->ID, 'seller_name',  true );
	if ( ! is_email( $to ) ) {
		return;
	}

	$subject = '[VMRA Classifieds] Your listing is live: ' . $post->post_title;
	$body  = "Hi " . ( $name ?: 'there' ) . ",\n\n";
	$body .= "Good news — the VMRA board approved your classified listing. It's live now:\n\n";
	$body .= get_permalink( $post ) . "\n\n";
	$body .= "A few housekeeping notes:\n\n";
	$body .= "• Buyers contact you directly using the info you chose to share.\n";
	$body .= "• When it sells, just reply to this email with SOLD in the subject and we'll\n";
	$body .= "  flip the badge on your listing so it shows as sold.\n";
	$body .= "• Questions, edits, or problems? Reply to this email — vmrainfo@gmail.com.\n\n";
	$body .= "Thanks for using the board.\n";
	$body .= "— VMRA\n";

	$headers = array( 'From: VMRA Classifieds <vmrainfo@gmail.com>' );
	wp_mail( $to, $subject, $body, $headers );
}

function vmra_classified_email_submitter_denial( $post, $reason ) {
	$to   = (string) get_post_meta( $post->ID, 'seller_email', true );
	$name = (string) get_post_meta( $post->ID, 'seller_name',  true );
	if ( ! is_email( $to ) ) {
		return;
	}

	$subject = '[VMRA Classifieds] Your listing was not approved: ' . $post->post_title;
	$body  = "Hi " . ( $name ?: 'there' ) . ",\n\n";
	$body .= "Thanks for submitting a listing to the VMRA Classifieds. After review, the board wasn't\n";
	$body .= "able to approve this one. Here's the reason:\n\n";
	$body .= "---\n" . $reason . "\n---\n\n";
	$body .= "What you can do:\n";
	$body .= "• If it was a fixable issue (photo, description, category), resubmit on\n";
	$body .= "  " . home_url( '/classifieds/' ) . "\n";
	$body .= "• If you think this was a mistake, reply to this email and the board will\n";
	$body .= "  take another look.\n\n";
	$body .= "Thanks for your understanding.\n";
	$body .= "— VMRA\n";

	$headers = array( 'From: VMRA Classifieds <vmrainfo@gmail.com>' );
	wp_mail( $to, $subject, $body, $headers );
}


// ---------------------------------------------------------------------------
// 7. Admin list columns — show the submitter + status at a glance so the
//    board doesn't have to click into each pending listing to triage.
// ---------------------------------------------------------------------------
add_filter( 'manage_vmra_classified_posts_columns', 'vmra_classified_admin_columns' );
function vmra_classified_admin_columns( $cols ) {
	// Preserve checkbox + title; inject our columns; keep date at the end.
	$new = array();
	foreach ( $cols as $k => $label ) {
		$new[ $k ] = $label;
		if ( $k === 'title' ) {
			$new['vmra_submitter'] = 'Submitter';
			$new['vmra_category']  = 'Category';
			$new['vmra_price']     = 'Price';
		}
	}
	return $new;
}

add_action( 'manage_vmra_classified_posts_custom_column', 'vmra_classified_admin_column_data', 10, 2 );
function vmra_classified_admin_column_data( $col, $post_id ) {
	switch ( $col ) {
		case 'vmra_submitter':
			$name  = (string) get_post_meta( $post_id, 'seller_name', true );
			$email = (string) get_post_meta( $post_id, 'seller_email', true );
			echo esc_html( $name ?: '—' );
			if ( $email ) {
				echo '<br><a href="mailto:' . esc_attr( $email ) . '" style="font-size:11px;color:#646970">' . esc_html( $email ) . '</a>';
			}
			break;

		case 'vmra_category':
			$terms = wp_get_object_terms( $post_id, 'vmra_classified_cat', array( 'fields' => 'names' ) );
			echo esc_html( is_array( $terms ) && ! empty( $terms ) ? implode( ', ', $terms ) : '—' );
			break;

		case 'vmra_price':
			echo esc_html( (string) get_post_meta( $post_id, 'price', true ) ?: '—' );
			break;
	}
}
