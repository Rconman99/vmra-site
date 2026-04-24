<?php
/**
 * Template for a single vmra_race post.
 * Shows round, date, track, event, winner, per-driver results.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();

while ( have_posts() ) : the_post();
	$round    = get_post_meta( get_the_ID(), 'round_number', true );
	$date     = get_post_meta( get_the_ID(), 'race_date',    true );
	$event    = get_post_meta( get_the_ID(), 'event_name',   true );
	$winner_note = get_post_meta( get_the_ID(), 'winner_note', true );
	$is_done  = (bool) get_post_meta( get_the_ID(), 'is_completed', true );
	$track_id = (int) get_post_meta( get_the_ID(), 'track', true );
	$winner_id = (int) get_post_meta( get_the_ID(), 'winner', true );
	$results  = function_exists( 'get_field' ) ? (array) get_field( 'results_table' ) : array();
	$track    = $track_id ? get_post( $track_id ) : null;
	$winner   = $winner_id ? get_post( $winner_id ) : null;
?>
<style>
.race-hero{padding:80px 5vw 50px;border-bottom:2px solid var(--race-red);background:linear-gradient(180deg,var(--asphalt-2),var(--asphalt))}
.race-hero-inner{max-width:1080px;margin:0 auto}
.race-eyebrow{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-size:.78rem;letter-spacing:.22em;text-transform:uppercase;margin-bottom:14px;display:flex;gap:14px;flex-wrap:wrap}
.race-eyebrow .round{color:var(--race-red)}
.race-hero h1{font-family:'Anton',sans-serif;font-size:clamp(2.4rem,5.5vw,4.2rem);line-height:1;letter-spacing:-.01em;text-transform:uppercase;margin-bottom:20px}
.race-hero .subtitle{font-size:1.15rem;color:var(--chalk-dim);max-width:72ch;margin-bottom:28px}
.race-meta-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:18px 28px;font-family:'JetBrains Mono',monospace;font-size:.82rem;letter-spacing:.08em;padding-top:20px;border-top:1px solid var(--grease)}
.race-meta-grid .k{color:var(--chalk-dim);display:block;font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;margin-bottom:4px}
.race-meta-grid .v{color:var(--chalk);font-weight:600}
.race-meta-grid .v.sodium{color:var(--sodium)}
main.race-body{max-width:1080px;margin:0 auto;padding:60px 5vw}
.track-card{display:grid;grid-template-columns:1.2fr 1fr;border:1px solid var(--grease);background:var(--asphalt-2);overflow:hidden;margin-bottom:50px}
.track-card .map{position:relative;background:var(--asphalt-3);min-height:280px}
.track-card .map iframe{display:block;width:100%;height:100%;border:0;filter:saturate(.9) contrast(1.05)}
.track-card .info{padding:28px 32px;display:flex;flex-direction:column;gap:14px;justify-content:center}
.track-card .info .label{font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.2em;text-transform:uppercase;color:var(--sodium)}
.track-card .info .name{font-family:'Anton',sans-serif;font-size:1.7rem;line-height:1.1;text-transform:uppercase;letter-spacing:.02em}
.track-card .info .addr{font-size:.98rem;color:var(--chalk);line-height:1.5}
.track-card .info .shape{font-family:'JetBrains Mono',monospace;font-size:.78rem;letter-spacing:.06em;color:var(--chalk-dim)}
.track-card .btns{display:flex;flex-direction:column;gap:8px;margin-top:8px}
.track-card .btn{display:inline-flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 18px;font-family:'JetBrains Mono',monospace;font-size:.78rem;letter-spacing:.12em;text-transform:uppercase;text-decoration:none;border:1px solid var(--grease);color:var(--chalk);background:var(--asphalt);transition:all .15s;font-weight:600}
.track-card .btn:hover{border-color:var(--race-red);color:var(--sodium)}
.track-card .btn.primary{background:var(--race-red);border-color:var(--race-red);color:var(--chalk)}
.track-card .btn.primary:hover{background:var(--asphalt);color:var(--sodium);border-color:var(--sodium)}
.upcoming-note{background:var(--asphalt-2);border-left:3px solid var(--sodium);padding:18px 24px;margin-bottom:50px;font-size:.95rem;color:var(--chalk-dim);line-height:1.6}
.upcoming-note strong{color:var(--chalk)}
.upcoming-note a{color:var(--sodium);text-decoration:none;border-bottom:1px solid var(--race-red);padding-bottom:1px}
@media (max-width:780px){.track-card{grid-template-columns:1fr}.track-card .map{min-height:240px}}
.winner-card{background:var(--asphalt-2);border:1px solid var(--grease);border-left:4px solid var(--race-red);padding:28px;margin-bottom:50px}
.winner-card .tag{font-family:'JetBrains Mono',monospace;color:var(--race-red);font-size:.7rem;letter-spacing:.22em;text-transform:uppercase;margin-bottom:10px}
.winner-card h2{font-family:'Anton',sans-serif;font-size:2rem;line-height:1.1;text-transform:uppercase;letter-spacing:.02em;margin-bottom:10px}
.winner-card p{color:var(--chalk-dim);margin:0}
.results-table{width:100%;border-collapse:collapse;font-family:'Space Grotesk',sans-serif}
.results-table th{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-size:.7rem;letter-spacing:.18em;text-transform:uppercase;text-align:left;padding:14px 12px;border-bottom:2px solid var(--race-red)}
.results-table td{padding:14px 12px;border-bottom:1px solid var(--grease)}
.results-table td.num{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-weight:700}
.results-table td.car{display:inline-block;font-family:'Archivo Black',sans-serif;background:var(--chalk);color:var(--asphalt);padding:2px 8px;border-radius:2px}
.results-table td.total{font-family:'Anton',sans-serif;font-size:1.4rem;color:var(--race-red);text-align:right}
</style>

<section class="race-hero"><div class="race-hero-inner">
	<div class="race-eyebrow">
		<?php if ( $round ) : ?><span class="round">§ Round <?php printf( '%02d', (int) $round ); ?> / 11</span><?php endif; ?>
		<?php if ( $date ) : echo '<span>' . esc_html( mysql2date( 'F j, Y', $date ) ) . '</span>'; endif; ?>
		<?php if ( $is_done ) : ?><span style="color:var(--sodium)">· Complete</span><?php endif; ?>
	</div>
	<h1><?php the_title(); ?></h1>
	<?php if ( $event ) : ?><p class="subtitle"><?php echo esc_html( $event ); ?><?php echo $track ? ' · ' . esc_html( get_the_title( $track ) ) : ''; ?></p><?php endif; ?>
	<div class="race-meta-grid">
		<?php if ( $track ) : ?>
			<div><span class="k">Track</span><span class="v"><a href="<?php echo esc_url( get_permalink( $track ) ); ?>" style="color:inherit;border-bottom:1px dashed var(--gold-rule)"><?php echo esc_html( get_the_title( $track ) ); ?></a></span></div>
		<?php endif; ?>
		<?php if ( $winner ) : ?>
			<div><span class="k">Winner</span><span class="v sodium"><a href="<?php echo esc_url( get_permalink( $winner ) ); ?>" style="color:inherit"><?php echo esc_html( get_the_title( $winner ) ); ?></a></span></div>
		<?php endif; ?>
		<div><span class="k">Cars</span><span class="v"><?php echo (int) count( $results ) ?: '—'; ?></span></div>
		<div><span class="k">Round Type</span><span class="v"><?php echo get_post_meta( get_the_ID(), 'is_points', true ) ? 'Points' : 'Special · Non-Points'; ?></span></div>
	</div>
</div></section>

<main class="race-body">

	<?php
	// Pull track address + shape + website from the schedule seed (matched by round number).
	// The track CPT may not have meta yet, so the seed JSON is the reliable source.
	$track_addr = '';
	$track_shape = '';
	$track_website = '';
	$track_name = $track ? get_the_title( $track ) : '';
	$seed = vmra_seed_data( 'schedule' );
	if ( $seed && ! empty( $seed['races'] ) ) {
		foreach ( $seed['races'] as $r ) {
			if ( (int) $r['round'] === (int) $round ) {
				$track_name = $r['track'] ?: $track_name;
				if ( ! empty( $seed['tracks'][ $r['track'] ] ) ) {
					$tinfo = $seed['tracks'][ $r['track'] ];
					$track_addr    = $tinfo['address']  ?? '';
					$track_shape   = $tinfo['shape']    ?? '';
					$track_website = $tinfo['website']  ?? '';
				}
				break;
			}
		}
	}
	if ( $track_addr ) :
		$enc_addr  = rawurlencode( $track_addr );
		$map_src   = 'https://maps.google.com/maps?q=' . $enc_addr . '&t=k&z=17&ie=UTF8&output=embed';
		$dir_href  = 'https://www.google.com/maps/dir/?api=1&destination=' . $enc_addr;
	?>
		<div class="track-card">
			<div class="map">
				<iframe src="<?php echo esc_url( $map_src ); ?>" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="<?php echo esc_attr( $track_name . ' satellite map' ); ?>"></iframe>
			</div>
			<div class="info">
				<span class="label"><?php echo $is_done ? esc_html__( 'Where it happened', 'vmra' ) : esc_html__( 'Where to be', 'vmra' ); ?></span>
				<span class="name"><?php echo esc_html( $track_name ); ?></span>
				<span class="addr"><?php echo esc_html( $track_addr ); ?></span>
				<?php if ( $track_shape ) : ?><span class="shape"><?php echo esc_html( $track_shape ); ?></span><?php endif; ?>
				<div class="btns">
					<a class="btn primary" href="<?php echo esc_url( $dir_href ); ?>" target="_blank" rel="noopener">Get Directions →</a>
					<?php if ( $track_website ) : ?><a class="btn" href="<?php echo esc_url( $track_website ); ?>" target="_blank" rel="noopener">Track Website ↗</a><?php endif; ?>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( ! $is_done && empty( $results ) ) : // Pre-race info note for upcoming rounds. ?>
		<div class="upcoming-note">
			<strong><?php esc_html_e( 'Pre-registration:', 'vmra' ); ?></strong>
			<?php if ( strpos( $track_name, 'Tri-City' ) !== false ) : ?>
				<?php esc_html_e( 'Tri-City Raceway uses an online sign-up — head to', 'vmra' ); ?>
				<a href="https://tricityraceway.com/drivers.html" target="_blank" rel="noopener">tricityraceway.com/drivers.html</a>
				<?php esc_html_e( 'for the form. Questions: contact the board at', 'vmra' ); ?>
				<a href="mailto:board@vmra.club">board@vmra.club</a>.
			<?php else : ?>
				<?php esc_html_e( 'Contact the board to register for this round at', 'vmra' ); ?>
				<a href="mailto:board@vmra.club">board@vmra.club</a>.
				<?php if ( $track_website ) : ?>
					<?php esc_html_e( 'Track gate info and pit pass details on the', 'vmra' ); ?>
					<a href="<?php echo esc_url( $track_website ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'track website', 'vmra' ); ?></a>.
				<?php endif; ?>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<?php if ( $winner_note ) : ?>
		<div class="winner-card">
			<div class="tag">Winner's Note</div>
			<h2><?php echo $winner ? esc_html( get_the_title( $winner ) ) : esc_html__( 'Race Note', 'vmra' ); ?></h2>
			<p><?php echo esc_html( $winner_note ); ?></p>
		</div>
	<?php endif; ?>

	<?php if ( ! empty( $results ) ) : ?>
		<h2 style="font-family:'Anton',sans-serif;font-size:2rem;text-transform:uppercase;letter-spacing:.02em;margin-bottom:22px"><?php esc_html_e( 'Full Results', 'vmra' ); ?></h2>
		<div style="overflow-x:auto"><table class="results-table">
			<thead><tr>
				<th>Pos</th><th>Car</th><th>Driver</th>
				<th style="text-align:center">Qualify</th><th style="text-align:center">Heat</th><th style="text-align:center">Main</th><th style="text-align:center">Show-up</th>
				<th style="text-align:right">Total</th>
			</tr></thead>
			<tbody>
				<?php
				usort( $results, function( $a, $b ) { return (int) ( $b['total_on_date'] ?? 0 ) - (int) ( $a['total_on_date'] ?? 0 ); } );
				foreach ( $results as $i => $r ) :
					$drv_id  = is_numeric( $r['driver'] ) ? (int) $r['driver'] : ( is_object( $r['driver'] ) ? $r['driver']->ID : 0 );
					$drv     = $drv_id ? get_post( $drv_id ) : null;
					$car_num = $drv ? get_post_meta( $drv_id, 'car_number', true ) : '';
				?>
					<tr>
						<td class="num"><?php printf( '%02d', $i + 1 ); ?></td>
						<td><span class="car"><?php echo esc_html( $car_num ); ?></span></td>
						<td><?php echo $drv ? '<a href="' . esc_url( get_permalink( $drv_id ) ) . '" style="color:inherit">' . esc_html( get_the_title( $drv_id ) ) . '</a>' : '—'; ?></td>
						<td style="text-align:center"><?php echo (int) ( $r['qualify_pts'] ?? 0 ); ?></td>
						<td style="text-align:center"><?php echo (int) ( $r['heat_pts'] ?? 0 ); ?></td>
						<td style="text-align:center"><?php echo (int) ( $r['main_pts'] ?? 0 ); ?></td>
						<td style="text-align:center"><?php echo (int) ( $r['showup_pts'] ?? 0 ); ?></td>
						<td class="total"><?php echo (int) ( $r['total_on_date'] ?? 0 ); ?></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table></div>
	<?php endif; ?>

	<?php if ( get_the_content() ) : ?>
		<div style="margin-top:50px;max-width:72ch;color:var(--chalk-dim);line-height:1.7"><?php the_content(); ?></div>
	<?php endif; ?>

	<p style="margin-top:60px;font-family:'JetBrains Mono',monospace;font-size:.8rem;letter-spacing:.12em;text-transform:uppercase;text-align:center">
		<a href="<?php echo esc_url( home_url( '/schedule/' ) ); ?>" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid var(--race-red);padding-bottom:2px">← Back to full schedule</a>
	</p>
</main>

<?php endwhile; get_footer();
