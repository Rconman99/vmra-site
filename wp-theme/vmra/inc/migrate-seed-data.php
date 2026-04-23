<?php
/**
 * One-click migration: seed the Driver / Track / Race / News CPTs from the
 * theme's /data/*.json files. Idempotent — safe to re-run; existing posts are
 * matched by slug/meta and updated, not duplicated.
 *
 * Admin page: Tools → VMRA Seed Data
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_menu', function () {
	add_management_page(
		__( 'VMRA Seed Data', 'vmra' ),
		__( 'VMRA Seed Data', 'vmra' ),
		'manage_options',
		'vmra-seed-data',
		'vmra_render_seed_data_page'
	);
} );

function vmra_render_seed_data_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( esc_html__( 'Admin only.', 'vmra' ) );
	}

	$result = null;
	if ( ! empty( $_POST['vmra_seed_go'] ) && check_admin_referer( 'vmra_seed_data' ) ) {
		$result = vmra_run_seed();
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'VMRA Seed Data', 'vmra' ); ?></h1>
		<p class="description" style="max-width:780px">
			<?php esc_html_e( "One-click import of the static site's JSON data into WP posts. Creates Driver, Track, Race, and News CPT entries if they don't exist, or updates them by slug. Safe to re-run.", 'vmra' ); ?>
		</p>

		<?php if ( $result ) : ?>
			<div class="notice notice-success"><p>
				<strong><?php esc_html_e( 'Seed complete.', 'vmra' ); ?></strong><br>
				<?php foreach ( $result as $k => $v ) : ?>
					<span style="display:inline-block;margin:6px 24px 0 0"><strong><?php echo esc_html( $k ); ?></strong>: <?php echo esc_html( $v['created'] ); ?> created, <?php echo esc_html( $v['updated'] ); ?> updated</span>
				<?php endforeach; ?>
			</p></div>
		<?php endif; ?>

		<form method="post">
			<?php wp_nonce_field( 'vmra_seed_data' ); ?>
			<p><button type="submit" name="vmra_seed_go" value="1" class="button button-primary button-large"><?php esc_html_e( 'Seed Drivers + Tracks + Races + News from JSON', 'vmra' ); ?></button></p>
			<p class="description"><?php esc_html_e( 'Reads wp-content/themes/vmra/data/{schedule,standings,news,results}.json.', 'vmra' ); ?></p>
		</form>

		<h2 style="margin-top:30px"><?php esc_html_e( 'What gets created', 'vmra' ); ?></h2>
		<ul style="list-style:disc;padding-left:20px;max-width:780px">
			<li><strong>Drivers</strong> — one post per entry in <code>standings.json</code> (name, slug, car_number meta).</li>
			<li><strong>Tracks</strong> — one post per entry in <code>schedule.json.tracks</code> (name, city/state, address, shape, length).</li>
			<li><strong>Races</strong> — one post per entry in <code>schedule.json.races</code> (round, date, event_name, track relation).</li>
			<li><strong>News</strong> — one post per entry in <code>news.json.items</code> (headline as title, snippet as body, byline, category).</li>
		</ul>
	</div>
	<?php
}

function vmra_run_seed() {
	$stats = array(
		'Drivers' => array( 'created' => 0, 'updated' => 0 ),
		'Tracks'  => array( 'created' => 0, 'updated' => 0 ),
		'Races'   => array( 'created' => 0, 'updated' => 0 ),
		'News'    => array( 'created' => 0, 'updated' => 0 ),
	);

	$read = function ( $name ) {
		$path = get_template_directory() . '/data/' . $name . '.json';
		if ( ! file_exists( $path ) ) return null;
		return json_decode( file_get_contents( $path ), true );
	};

	// --- Drivers ---
	$standings = $read( 'standings' );
	$driver_map = array(); // car_number => post_id
	if ( ! empty( $standings['drivers'] ) ) {
		foreach ( $standings['drivers'] as $d ) {
			$name = $d['name'];
			$slug = sanitize_title( $name . '-' . $d['car'] );
			$existing = get_page_by_path( $slug, OBJECT, 'vmra_driver' );
			if ( $existing ) {
				wp_update_post( array( 'ID' => $existing->ID, 'post_title' => $name ) );
				$pid = $existing->ID;
				$stats['Drivers']['updated']++;
			} else {
				$pid = wp_insert_post( array(
					'post_type'   => 'vmra_driver',
					'post_status' => 'publish',
					'post_title'  => $name,
					'post_name'   => $slug,
				) );
				$stats['Drivers']['created']++;
			}
			update_post_meta( $pid, 'car_number', (string) $d['car'] );
			$driver_map[ (string) $d['car'] ] = $pid;
		}
	}

	// --- Tracks ---
	$schedule = $read( 'schedule' );
	$track_map = array(); // track name => post_id
	if ( ! empty( $schedule['tracks'] ) ) {
		foreach ( $schedule['tracks'] as $name => $info ) {
			$slug = sanitize_title( $name );
			$existing = get_page_by_path( $slug, OBJECT, 'vmra_track' );
			$data = array(
				'post_type'   => 'vmra_track',
				'post_status' => 'publish',
				'post_title'  => $name,
				'post_name'   => $slug,
			);
			if ( $existing ) {
				$data['ID'] = $existing->ID;
				wp_update_post( $data );
				$pid = $existing->ID;
				$stats['Tracks']['updated']++;
			} else {
				$pid = wp_insert_post( $data );
				$stats['Tracks']['created']++;
			}
			$track_map[ $name ] = $pid;
			if ( ! empty( $info['address'] ) ) update_post_meta( $pid, 'address', $info['address'] );
			if ( ! empty( $info['shape'] ) )   update_post_meta( $pid, 'shape',   $info['shape'] );
			if ( ! empty( $info['length'] ) )  update_post_meta( $pid, 'length',  $info['length'] );
			if ( ! empty( $info['city_state'] ) ) update_post_meta( $pid, 'city_state', $info['city_state'] );
			if ( ! empty( $info['website'] ) ) update_post_meta( $pid, 'website', $info['website'] );
		}
	}

	// --- Races ---
	if ( ! empty( $schedule['races'] ) ) {
		foreach ( $schedule['races'] as $r ) {
			$slug  = 'round-' . str_pad( (string) ( $r['round'] ?? 0 ), 2, '0', STR_PAD_LEFT );
			$title = sprintf( 'Round %02d · %s', (int) ( $r['round'] ?? 0 ), $r['event_name'] ?? ( $r['track'] ?? 'Race' ) );
			$existing = get_page_by_path( $slug, OBJECT, 'vmra_race' );
			$data = array(
				'post_type'   => 'vmra_race',
				'post_status' => 'publish',
				'post_title'  => $title,
				'post_name'   => $slug,
			);
			if ( $existing ) {
				$data['ID'] = $existing->ID;
				wp_update_post( $data );
				$pid = $existing->ID;
				$stats['Races']['updated']++;
			} else {
				$pid = wp_insert_post( $data );
				$stats['Races']['created']++;
			}
			update_post_meta( $pid, 'round_number', (int) ( $r['round'] ?? 0 ) );
			update_post_meta( $pid, 'race_date',    $r['date'] ?? '' );
			update_post_meta( $pid, 'event_name',   $r['event_name'] ?? '' );
			update_post_meta( $pid, 'is_points',    ! isset( $r['tag'] ) || $r['tag'] !== 'exhibition' );
			if ( ! empty( $r['track'] ) && isset( $track_map[ $r['track'] ] ) ) {
				update_post_meta( $pid, 'track', $track_map[ $r['track'] ] );
			}
		}
	}

	// --- News ---
	$news = $read( 'news' );
	if ( ! empty( $news['items'] ) ) {
		foreach ( $news['items'] as $i => $n ) {
			$slug = sanitize_title( ( $n['date'] ?? 'news' ) . '-' . substr( $n['headline'] ?? 'article', 0, 50 ) );
			$existing = get_page_by_path( $slug, OBJECT, 'vmra_news' );
			$data = array(
				'post_type'    => 'vmra_news',
				'post_status'  => 'publish',
				'post_title'   => $n['headline'] ?? 'News',
				'post_name'    => $slug,
				'post_excerpt' => $n['snippet'] ?? '',
				'post_content' => $n['snippet'] ?? '',
				'post_date'    => ! empty( $n['date'] ) ? $n['date'] . ' 12:00:00' : current_time( 'mysql' ),
			);
			if ( $existing ) {
				$data['ID'] = $existing->ID;
				wp_update_post( $data );
				$pid = $existing->ID;
				$stats['News']['updated']++;
			} else {
				$pid = wp_insert_post( $data );
				$stats['News']['created']++;
			}
			if ( function_exists( 'update_field' ) ) {
				if ( ! empty( $n['byline'] ) ) update_field( 'byline', $n['byline'], $pid );
				if ( ! empty( $n['feature'] ) ) update_field( 'is_feature', true, $pid );
				if ( ! empty( $n['car_num'] ) ) update_field( 'card_car_number', $n['car_num'], $pid );
			}
		}
	}

	return $stats;
}
