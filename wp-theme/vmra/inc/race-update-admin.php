<?php
/**
 * Phase 3 — Race Update admin tool.
 *
 * Adds "VMRA Race Update" under the Tools menu. The board pastes the weekly
 * race-update email (same template as scripts/apply-race-update.py) and the
 * tool:
 *   1. Parses the email with PHP regex (mirrors the Python parser).
 *   2. Shows a preview of what will be created/updated.
 *   3. On confirm: creates/updates the vmra_race post + creates a vmra_news
 *      article + fills the ACF results_table repeater.
 *
 * The page is permission-gated to users with 'manage_options' (admins) and
 * 'edit_others_posts' (editors) so the board can use it without giving full
 * admin access.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_management_page(
		__( 'VMRA Race Update', 'vmra' ),
		__( 'VMRA Race Update', 'vmra' ),
		'edit_others_posts',
		'vmra-race-update',
		'vmra_render_race_update_page'
	);
} );

/**
 * Render the admin page.
 */
function vmra_render_race_update_page() {
	if ( ! current_user_can( 'edit_others_posts' ) ) {
		wp_die( esc_html__( 'You do not have permission to post race updates.', 'vmra' ) );
	}

	$action = isset( $_POST['vmra_action'] ) ? sanitize_text_field( wp_unslash( $_POST['vmra_action'] ) ) : '';
	$email  = isset( $_POST['vmra_email_text'] ) ? (string) wp_unslash( $_POST['vmra_email_text'] ) : '';
	$parsed = null;
	$applied = null;

	if ( $action && ! empty( $_POST['vmra_nonce'] ) && wp_verify_nonce( $_POST['vmra_nonce'], 'vmra_race_update' ) ) {
		$parsed = vmra_parse_race_email( $email );
		if ( 'apply' === $action && ! empty( $parsed['ok'] ) ) {
			$applied = vmra_apply_race_update( $parsed );
		}
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'VMRA Race Update', 'vmra' ); ?></h1>
		<p class="description" style="max-width:780px">
			<?php esc_html_e( 'Paste the templated race-update email below. The tool parses it, shows a preview, and on confirm creates a Race post, a News article, and fills in the per-driver results table.', 'vmra' ); ?>
		</p>

		<?php if ( $applied ) : ?>
			<div class="notice notice-success"><p>
				<strong><?php esc_html_e( 'Race update applied.', 'vmra' ); ?></strong>
				<?php echo wp_kses_post( $applied['message'] ); ?>
			</p></div>
		<?php endif; ?>

		<?php if ( $parsed && empty( $parsed['ok'] ) ) : ?>
			<div class="notice notice-error"><p><strong><?php esc_html_e( 'Parse error:', 'vmra' ); ?></strong> <?php echo esc_html( $parsed['error'] ); ?></p></div>
		<?php endif; ?>

		<form method="post" style="margin-top:20px">
			<?php wp_nonce_field( 'vmra_race_update', 'vmra_nonce' ); ?>

			<h2><?php esc_html_e( 'Email Text', 'vmra' ); ?></h2>
			<textarea name="vmra_email_text" rows="14" style="width:100%;font-family:Menlo,Monaco,monospace;font-size:12px" placeholder="Paste the race-update email here..."><?php echo esc_textarea( $email ); ?></textarea>

			<p>
				<button type="submit" name="vmra_action" value="parse" class="button button-secondary">
					<?php esc_html_e( '1. Parse & Preview', 'vmra' ); ?>
				</button>
			</p>
		</form>

		<?php if ( $parsed && ! empty( $parsed['ok'] ) ) : ?>
			<h2><?php esc_html_e( 'Preview', 'vmra' ); ?></h2>
			<table class="widefat striped" style="max-width:900px">
				<tbody>
					<tr><th style="width:200px"><?php esc_html_e( 'Round', 'vmra' ); ?></th><td><?php echo (int) $parsed['round']; ?></td></tr>
					<tr><th><?php esc_html_e( 'Date', 'vmra' ); ?></th><td><?php echo esc_html( $parsed['date'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Track', 'vmra' ); ?></th><td><?php echo esc_html( $parsed['track'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Event', 'vmra' ); ?></th><td><?php echo esc_html( $parsed['event'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Winner', 'vmra' ); ?></th><td><?php echo esc_html( $parsed['winner'] ); ?></td></tr>
					<tr><th><?php esc_html_e( 'Results rows parsed', 'vmra' ); ?></th><td><?php echo count( $parsed['results'] ); ?></td></tr>
				</tbody>
			</table>

			<h3><?php esc_html_e( 'Per-driver results', 'vmra' ); ?></h3>
			<table class="widefat striped">
				<thead><tr>
					<th><?php esc_html_e( 'Car', 'vmra' ); ?></th>
					<th><?php esc_html_e( 'Driver', 'vmra' ); ?></th>
					<th><?php esc_html_e( 'Qualify', 'vmra' ); ?></th>
					<th><?php esc_html_e( 'Heat', 'vmra' ); ?></th>
					<th><?php esc_html_e( 'Main', 'vmra' ); ?></th>
					<th><?php esc_html_e( 'Show-up', 'vmra' ); ?></th>
					<th><?php esc_html_e( 'Total', 'vmra' ); ?></th>
				</tr></thead>
				<tbody>
					<?php foreach ( $parsed['results'] as $r ) : ?>
						<tr>
							<td><strong>#<?php echo esc_html( $r['car'] ); ?></strong></td>
							<td><?php echo esc_html( $r['driver'] ); ?></td>
							<td><?php echo (int) $r['qualify']; ?></td>
							<td><?php echo (int) $r['heat']; ?></td>
							<td><?php echo (int) $r['main']; ?></td>
							<td><?php echo (int) $r['showup']; ?></td>
							<td><strong><?php echo (int) $r['total']; ?></strong></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<form method="post" style="margin-top:24px">
				<?php wp_nonce_field( 'vmra_race_update', 'vmra_nonce' ); ?>
				<input type="hidden" name="vmra_email_text" value="<?php echo esc_attr( $email ); ?>">
				<button type="submit" name="vmra_action" value="apply" class="button button-primary button-large">
					<?php esc_html_e( '2. Apply — Create Race + News + Update Standings', 'vmra' ); ?>
				</button>
			</form>
		<?php endif; ?>

		<hr style="margin-top:40px">
		<h2><?php esc_html_e( 'Email template reference', 'vmra' ); ?></h2>
		<details>
			<summary><?php esc_html_e( 'Show the expected email format', 'vmra' ); ?></summary>
			<pre style="background:#f4f4f6;padding:16px;max-width:900px;white-space:pre-wrap">
ROUND: 2
DATE: 2026-04-25
TRACK: Evergreen Speedway
EVENT: Grocery Outlet Night
WINNER: Kahl Cheth #23

RESULTS
Car | Driver         | Qualify | Heat | Main | Show-up
23  | Kahl Cheth     | 19      | 0    | 25   | 20
8   | Jason Quatsoe  | 20      | 0    | 20   | 20
22  | Steve Woods    | 18      | 0    | 19   | 20
...

NOTES
(optional: a line or two of race color)
			</pre>
		</details>
	</div>
	<?php
}

/**
 * Parse a pasted race-update email into a structured array.
 *
 * @param string $text The raw email body.
 * @return array       { ok: bool, round, date, track, event, winner, results: [], notes, error? }
 */
function vmra_parse_race_email( $text ) {
	$out = array( 'ok' => false, 'results' => array() );
	if ( ! trim( $text ) ) {
		$out['error'] = 'Email text is empty.';
		return $out;
	}

	// Header lines — ROUND, DATE, TRACK, EVENT, WINNER
	$headers = array( 'round', 'date', 'track', 'event', 'winner' );
	foreach ( $headers as $key ) {
		if ( preg_match( '/^\s*' . $key . '\s*:\s*(.+)$/im', $text, $m ) ) {
			$val = trim( $m[1] );
			$out[ $key ] = $key === 'round' ? (int) $val : $val;
		} else {
			$out[ $key ] = '';
		}
	}

	// Results rows — lines with 6 pipe-separated columns, skipping header + separator rows.
	$lines = preg_split( '/\r?\n/', $text );
	foreach ( $lines as $line ) {
		if ( strpos( $line, '|' ) === false ) continue;
		$cols = array_map( 'trim', explode( '|', $line ) );
		if ( count( $cols ) < 6 ) continue;
		// Skip header / separator
		if ( stripos( $cols[0], 'car' ) === 0 ) continue;
		if ( preg_match( '/^[-=]+$/', $cols[0] ) ) continue;
		if ( ! preg_match( '/^\d[\w]*$/', $cols[0] ) ) continue; // Car number must start with digit
		$q = (int) ( $cols[2] ?? 0 );
		$h = (int) ( $cols[3] ?? 0 );
		$m = (int) ( $cols[4] ?? 0 );
		$s = (int) ( $cols[5] ?? 0 );
		$out['results'][] = array(
			'car'     => $cols[0],
			'driver'  => $cols[1],
			'qualify' => $q,
			'heat'    => $h,
			'main'    => $m,
			'showup'  => $s,
			'total'   => $q + $h + $m + $s,
		);
	}

	// Notes (optional)
	if ( preg_match( '/^\s*NOTES\s*\n(.+)$/ims', $text, $m ) ) {
		$out['notes'] = trim( $m[1] );
	} else {
		$out['notes'] = '';
	}

	if ( ! $out['round'] || ! $out['date'] || empty( $out['results'] ) ) {
		$out['error'] = 'Could not find required fields (ROUND, DATE) or results rows.';
		return $out;
	}

	$out['ok'] = true;
	return $out;
}

/**
 * Apply a parsed race update: create/update Race post, create News post, fill results.
 *
 * @param array $p Parsed payload from vmra_parse_race_email().
 * @return array   { ok: bool, message }
 */
function vmra_apply_race_update( $p ) {
	// Find existing race by round number, or create new.
	$existing = get_posts( array(
		'post_type'   => 'vmra_race',
		'meta_key'    => 'round_number',
		'meta_value'  => (int) $p['round'],
		'numberposts' => 1,
	) );

	$race_title = sprintf( 'Round %02d · %s', (int) $p['round'], $p['event'] ?: $p['track'] );
	$race_body  = $p['notes'] ?: '';

	if ( ! empty( $existing ) ) {
		$race_id = $existing[0]->ID;
		wp_update_post( array( 'ID' => $race_id, 'post_title' => $race_title, 'post_content' => $race_body ) );
		$created = false;
	} else {
		$race_id = wp_insert_post( array(
			'post_type'   => 'vmra_race',
			'post_status' => 'publish',
			'post_title'  => $race_title,
			'post_content' => $race_body,
		) );
		$created = true;
	}

	if ( is_wp_error( $race_id ) || ! $race_id ) {
		return array( 'ok' => false, 'message' => 'Failed to create/update race post.' );
	}

	// Post meta
	update_post_meta( $race_id, 'round_number', (int) $p['round'] );
	update_post_meta( $race_id, 'race_date',    $p['date'] );
	update_post_meta( $race_id, 'event_name',   $p['event'] );
	update_post_meta( $race_id, 'is_completed', true );
	update_post_meta( $race_id, 'winner_note',  $p['notes'] );

	// Try to find track by name
	$track_post = get_page_by_title( $p['track'], OBJECT, 'vmra_track' );
	if ( $track_post ) {
		update_post_meta( $race_id, 'track', $track_post->ID );
	}

	// Resolve winner driver by "Driver Name #Car"
	$winner_car = '';
	if ( preg_match( '/#(\S+)/', $p['winner'], $m ) ) { $winner_car = $m[1]; }
	$winner_id = 0;
	if ( $winner_car ) {
		$winner_posts = get_posts( array(
			'post_type' => 'vmra_driver',
			'meta_key'  => 'car_number',
			'meta_value' => $winner_car,
			'numberposts' => 1,
		) );
		if ( $winner_posts ) { $winner_id = $winner_posts[0]->ID; }
	}
	if ( $winner_id ) { update_post_meta( $race_id, 'winner', $winner_id ); }

	// Fill ACF results_table repeater (if ACF active + function_exists)
	if ( function_exists( 'update_field' ) ) {
		$rows = array();
		foreach ( $p['results'] as $r ) {
			// Resolve driver by car_number
			$drv = get_posts( array(
				'post_type' => 'vmra_driver',
				'meta_key'  => 'car_number',
				'meta_value' => $r['car'],
				'numberposts' => 1,
			) );
			$rows[] = array(
				'driver'        => $drv ? $drv[0]->ID : 0,
				'qualify_pts'   => (int) $r['qualify'],
				'heat_pts'      => (int) $r['heat'],
				'main_pts'      => (int) $r['main'],
				'showup_pts'    => (int) $r['showup'],
				'total_on_date' => (int) $r['total'],
			);
		}
		update_field( 'results_table', $rows, $race_id );
	}

	// Create a News post recapping the race
	$news_title = sprintf( '%s Wins Round %02d at %s', $p['winner'] ?: 'Winner TBD', (int) $p['round'], $p['track'] );
	$news_body  = sprintf(
		"Round %02d of the 2026 VMRA tour ran at %s on %s. %s took the %s. Full results on the standings page.",
		(int) $p['round'],
		$p['track'],
		$p['date'],
		$p['winner'] ?: 'The winner',
		$p['event'] ?: 'main event'
	);
	if ( ! empty( $p['notes'] ) ) {
		$news_body .= "\n\n" . $p['notes'];
	}
	$news_id = wp_insert_post( array(
		'post_type'   => 'vmra_news',
		'post_status' => 'publish',
		'post_title'  => $news_title,
		'post_content' => $news_body,
	) );
	if ( is_wp_error( $news_id ) ) { $news_id = 0; }
	if ( $news_id && function_exists( 'update_field' ) ) {
		update_field( 'byline', 'The VMRA Desk', $news_id );
		update_field( 'related_race', $race_id, $news_id );
	}

	$verb = $created ? 'Created' : 'Updated';
	$race_link = get_edit_post_link( $race_id );
	$news_link = $news_id ? get_edit_post_link( $news_id ) : '';
	$msg = sprintf(
		'%s race post <a href="%s">%s</a>',
		esc_html( $verb ),
		esc_url( $race_link ),
		esc_html( $race_title )
	);
	if ( $news_link ) {
		$msg .= sprintf( ' + created news article <a href="%s">%s</a>', esc_url( $news_link ), esc_html( $news_title ) );
	}
	$msg .= sprintf( '. Parsed %d driver results.', count( $p['results'] ) );

	return array( 'ok' => true, 'message' => $msg );
}
