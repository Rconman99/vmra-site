<?php
/**
 * Template for a single vmra_driver post.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();
	$car      = get_post_meta( get_the_ID(), 'car_number',    true );
	$home     = get_post_meta( get_the_ID(), 'hometown',      true );
	$rookie   = (bool) get_post_meta( get_the_ID(), 'is_rookie', true );
	$titles   = (int) get_post_meta( get_the_ID(), 'championships', true );
	$defending = (bool) get_post_meta( get_the_ID(), 'defending_champion', true );
	$photo    = function_exists( 'get_field' ) ? get_field( 'car_photo' ) : null;
	$photo_url = is_array( $photo ) ? $photo['url'] : ( $photo ?: '' );

	// Find all races where this driver has results
	$race_query = new WP_Query( array(
		'post_type' => 'vmra_race',
		'posts_per_page' => -1,
		'orderby' => 'meta_value_num',
		'meta_key' => 'round_number',
		'order' => 'ASC',
	) );
	$driver_id = get_the_ID();
	$season_total = 0;
	$race_rows = array();
	while ( $race_query->have_posts() ) : $race_query->the_post();
		$rid = get_the_ID();
		$round = get_post_meta( $rid, 'round_number', true );
		$track_id = (int) get_post_meta( $rid, 'track', true );
		$track_title = $track_id ? get_the_title( $track_id ) : '';
		$rows = function_exists( 'get_field' ) ? (array) get_field( 'results_table', $rid ) : array();
		foreach ( $rows as $row ) {
			$drv_id = is_numeric( $row['driver'] ) ? (int) $row['driver'] : ( is_object( $row['driver'] ) ? $row['driver']->ID : 0 );
			if ( $drv_id === $driver_id ) {
				$race_rows[] = array(
					'race_id' => $rid,
					'round'   => $round,
					'track'   => $track_title,
					'total'   => (int) ( $row['total_on_date'] ?? 0 ),
					'main'    => (int) ( $row['main_pts'] ?? 0 ),
				);
				$season_total += (int) ( $row['total_on_date'] ?? 0 );
			}
		}
	endwhile;
	wp_reset_postdata();

	// Fallback — if no WP race-update entries exist yet for this driver,
	// pull the current point total from data/standings.json by car number.
	// Keeps driver pages accurate between race night and the board entering
	// the round via the race-update admin tool.
	$rounds_raced = count( $race_rows );
	if ( $season_total === 0 && $car && function_exists( 'vmra_seed_data' ) ) {
		$standings = vmra_seed_data( 'standings' );
		if ( is_array( $standings ) && ! empty( $standings['drivers'] ) ) {
			foreach ( $standings['drivers'] as $row ) {
				if ( isset( $row['car'] ) && (string) $row['car'] === (string) $car ) {
					$season_total = (int) ( $row['points'] ?? 0 );
					// If they scored any points, they raced at least the
					// rounds_completed count. Zero-point entries are no-shows.
					if ( $season_total > 0 ) {
						$rounds_raced = (int) ( $standings['rounds_completed'] ?? 1 );
					}
					break;
				}
			}
		}
	}
?>
<style>
.driver-hero{padding:0 0 40px;border-bottom:2px solid var(--race-red);background:linear-gradient(135deg,var(--oxblood-deep),var(--asphalt) 60%)}
.driver-hero-grid{max-width:1080px;margin:0 auto;padding:0 5vw;display:grid;grid-template-columns:minmax(280px,360px) 1fr;gap:40px;align-items:center;min-height:380px}
.driver-plate{background:var(--race-red);aspect-ratio:1/1;display:grid;place-items:center;font-family:'Anton',sans-serif;font-size:clamp(7rem,16vw,11rem);color:var(--chalk);letter-spacing:-.04em;text-shadow:6px 6px 0 rgba(0,0,0,.35);border:4px solid var(--chalk);position:relative}
.driver-plate.photo{background-size:cover;background-position:center;font-size:0}
.driver-copy h1{font-family:'Anton',sans-serif;font-size:clamp(2.4rem,6vw,4.6rem);line-height:.95;text-transform:uppercase;letter-spacing:-.01em;margin-bottom:16px}
.driver-copy .badges{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:18px}
.driver-copy .badge{font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.22em;text-transform:uppercase;padding:6px 12px;background:var(--asphalt-2);border:1px solid var(--grease)}
.driver-copy .badge.defending{border-color:var(--race-red);color:var(--race-red)}
.driver-copy .badge.rookie{border-color:var(--sodium);color:var(--sodium)}
.driver-copy .home{font-family:'JetBrains Mono',monospace;font-size:.95rem;letter-spacing:.1em;text-transform:uppercase;color:var(--chalk-dim);margin-bottom:20px}
.driver-stats{display:flex;gap:30px;flex-wrap:wrap;padding-top:18px;border-top:1px solid var(--grease);font-family:'JetBrains Mono',monospace}
.driver-stats .stat .n{display:block;font-family:'Anton',sans-serif;font-size:2.4rem;color:var(--sodium);line-height:1}
.driver-stats .stat .l{font-size:.68rem;letter-spacing:.2em;text-transform:uppercase;color:var(--chalk-dim);display:block;margin-top:4px}
main.driver-body{max-width:1080px;margin:0 auto;padding:60px 5vw}
.results-table{width:100%;border-collapse:collapse;font-family:'Space Grotesk',sans-serif}
.results-table th{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-size:.7rem;letter-spacing:.18em;text-transform:uppercase;text-align:left;padding:14px 12px;border-bottom:2px solid var(--race-red)}
.results-table td{padding:14px 12px;border-bottom:1px solid var(--grease)}
.results-table td.total{font-family:'Anton',sans-serif;font-size:1.3rem;color:var(--race-red);text-align:right}
@media(max-width:760px){.driver-hero-grid{grid-template-columns:1fr;padding:40px 5vw 30px;min-height:auto}}
</style>

<section class="driver-hero"><div class="driver-hero-grid">
	<div class="driver-plate <?php echo $photo_url ? 'photo' : ''; ?>" style="<?php echo $photo_url ? 'background-image:url(' . esc_url( $photo_url ) . ')' : ''; ?>">
		<?php echo esc_html( $car ); ?>
	</div>
	<div class="driver-copy">
		<div class="badges">
			<?php if ( $defending ) : ?><span class="badge defending">Defending Champion</span><?php endif; ?>
			<?php if ( $rookie ) : ?><span class="badge rookie">Rookie</span><?php endif; ?>
			<span class="badge">Car #<?php echo esc_html( $car ); ?></span>
		</div>
		<h1><?php the_title(); ?></h1>
		<?php if ( $home ) : ?><div class="home"><?php echo esc_html( $home ); ?></div><?php endif; ?>
		<div class="driver-stats">
			<div class="stat"><span class="n"><?php echo $season_total; ?></span><span class="l">2026 Points</span></div>
			<div class="stat"><span class="n"><?php echo (int) $rounds_raced; ?></span><span class="l">Rounds Raced</span></div>
			<div class="stat"><span class="n"><?php echo $titles; ?></span><span class="l">Championships</span></div>
		</div>
	</div>
</div></section>

<main id="main-content" tabindex="-1" class="driver-body">
	<?php if ( get_the_content() ) : ?>
		<div style="max-width:72ch;color:var(--chalk-dim);line-height:1.7;margin-bottom:50px"><?php the_content(); ?></div>
	<?php endif; ?>

	<?php if ( ! empty( $race_rows ) ) : ?>
		<h2 style="font-family:'Anton',sans-serif;font-size:2rem;text-transform:uppercase;letter-spacing:.02em;margin-bottom:22px"><?php esc_html_e( 'Round-by-Round', 'vmra' ); ?></h2>
		<div style="overflow-x:auto"><table class="results-table">
			<thead><tr><th>Round</th><th>Track</th><th style="text-align:center">Main</th><th style="text-align:right">Total</th></tr></thead>
			<tbody>
				<?php foreach ( $race_rows as $r ) : ?>
					<tr>
						<td><strong><a href="<?php echo esc_url( get_permalink( $r['race_id'] ) ); ?>" style="color:var(--sodium);text-decoration:none">R<?php printf( '%02d', (int) $r['round'] ); ?></a></strong></td>
						<td><?php echo esc_html( $r['track'] ); ?></td>
						<td style="text-align:center"><?php echo (int) $r['main']; ?></td>
						<td class="total"><?php echo (int) $r['total']; ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table></div>
	<?php endif; ?>

	<p style="margin-top:60px;font-family:'JetBrains Mono',monospace;font-size:.8rem;letter-spacing:.12em;text-transform:uppercase;text-align:center">
		<a href="<?php echo esc_url( home_url( '/racers/' ) ); ?>" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid var(--race-red);padding-bottom:2px">← All VMRA drivers</a>
	</p>
</main>

<?php endwhile; get_footer();
