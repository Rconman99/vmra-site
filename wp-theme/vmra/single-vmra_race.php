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
