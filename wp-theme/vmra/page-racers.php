<?php
/**
 * /racers/ — driver grid pulled live from the vmra_driver CPT.
 * Each card uses the driver's featured image (real car photo) with
 * fallback to a big car-number plate when no photo is attached yet.
 * Sort: standings order (highest 2026 points first, then alphabetically).
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$standings = vmra_seed_data( 'standings' );
$points_by_car = array();
if ( ! empty( $standings['drivers'] ) ) {
	foreach ( $standings['drivers'] as $d ) {
		$points_by_car[ (string) $d['car'] ] = (int) $d['points'];
	}
}

$drivers = get_posts( array(
	'post_type'   => 'vmra_driver',
	'numberposts' => -1,
	'orderby'     => 'title',
	'order'       => 'ASC',
) );
// Sort by YTD points desc (drivers without standings go last, alpha).
usort( $drivers, function ( $a, $b ) use ( $points_by_car ) {
	$ca = (string) get_post_meta( $a->ID, 'car_number', true );
	$cb = (string) get_post_meta( $b->ID, 'car_number', true );
	$pa = $points_by_car[ $ca ] ?? -1;
	$pb = $points_by_car[ $cb ] ?? -1;
	if ( $pa === $pb ) return strcmp( $a->post_title, $b->post_title );
	return $pb - $pa;
} );

get_header(); ?>

<style>
.hero{padding:80px 5vw 50px;border-bottom:2px solid var(--race-red);background:linear-gradient(180deg,var(--asphalt-2),var(--asphalt))}
.hero-inner{max-width:1280px;margin:0 auto}
.eyebrow{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-size:.78rem;letter-spacing:.22em;text-transform:uppercase;margin-bottom:14px}
h1{font-family:'Anton',sans-serif;font-size:clamp(2.5rem,6vw,4.5rem);letter-spacing:.02em;line-height:1;margin-bottom:18px}
.lede{font-size:1.1rem;color:var(--chalk-dim);max-width:72ch;line-height:1.55}
main.racers{max-width:1280px;margin:0 auto;padding:60px 5vw}
.driver-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:24px}
.driver-tile{position:relative;background:var(--asphalt-2);border:1px solid var(--grease);overflow:hidden;color:inherit;text-decoration:none;display:flex;flex-direction:column;transition:border-color .2s,transform .2s}
.driver-tile:hover{border-color:var(--race-red);transform:translateY(-3px)}
.driver-tile .photo{aspect-ratio:4/3;background:linear-gradient(160deg,var(--engine-blue) 0%,var(--asphalt) 100%);display:grid;place-items:center;overflow:hidden;position:relative}
.driver-tile .photo img{width:100%;height:100%;object-fit:cover;display:block}
.driver-tile .car-plate-big{font-family:'Anton',sans-serif;font-size:clamp(4rem,10vw,6rem);line-height:.8;color:var(--chalk);text-shadow:3px 3px 0 rgba(0,0,0,.4);letter-spacing:-.03em}
.driver-tile .car-chip{position:absolute;top:12px;right:12px;font-family:'Archivo Black',sans-serif;background:var(--chalk);color:var(--asphalt);padding:4px 10px;font-size:.95rem;border-radius:2px}
.driver-tile .rank-chip{position:absolute;top:12px;left:12px;font-family:'JetBrains Mono',monospace;background:var(--race-red);color:var(--chalk);padding:4px 10px;font-size:.68rem;letter-spacing:.18em;text-transform:uppercase;font-weight:700}
.driver-tile .rank-chip.p1{background:var(--sodium);color:var(--asphalt)}
.driver-tile .body{padding:18px 20px;display:flex;flex-direction:column;gap:6px}
.driver-tile h3{font-family:'Anton',sans-serif;font-size:1.4rem;text-transform:uppercase;letter-spacing:.02em;line-height:1.05}
.driver-tile .meta{font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:var(--chalk-dim);display:flex;justify-content:space-between;align-items:center;gap:10px}
.driver-tile .pts{color:var(--sodium);font-weight:700}
.driver-tile .badges{display:flex;gap:6px;flex-wrap:wrap;margin-top:4px}
.driver-tile .badge{font-family:'JetBrains Mono',monospace;font-size:.58rem;letter-spacing:.2em;text-transform:uppercase;padding:3px 8px;border:1px solid var(--grease);color:var(--chalk-dim)}
.driver-tile .badge.rookie{border-color:var(--sodium);color:var(--sodium)}
.driver-tile .badge.champ{border-color:var(--race-red);color:var(--race-red)}
.legend{font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.14em;text-transform:uppercase;color:var(--chalk-dim);text-align:center;margin-top:40px}
.legend a{color:var(--sodium);text-decoration:none;border-bottom:1px solid var(--race-red);padding-bottom:2px}
</style>

<section class="hero"><div class="hero-inner">
	<span class="eyebrow"><?php esc_html_e( '§ 2026 Grid · 40th Anniversary Season', 'vmra' ); ?></span>
	<h1><?php esc_html_e( 'The Drivers.', 'vmra' ); ?></h1>
	<p class="lede"><?php esc_html_e( 'Twenty-three vintage modifieds on the 2026 tour — defending champs, three-time title holders, rookies, owner-drivers. Every car hand-built in someone\'s shop. Click any driver to see their round-by-round results and story.', 'vmra' ); ?></p>
</div></section>

<main id="main-content" tabindex="-1" class="racers">
	<div class="driver-grid">
		<?php
		$i = 0;
		foreach ( $drivers as $d ) :
			$car        = (string) get_post_meta( $d->ID, 'car_number',     true );
			$home       = (string) get_post_meta( $d->ID, 'hometown',       true );
			$rookie     = (bool)   get_post_meta( $d->ID, 'is_rookie',      true );
			$titles     = (int)    get_post_meta( $d->ID, 'championships',  true );
			$defending  = (bool)   get_post_meta( $d->ID, 'defending_champion', true );
			$pts        = $points_by_car[ $car ] ?? null;
			$rank       = null;
			if ( $pts !== null && ! empty( $standings['drivers'] ) ) {
				foreach ( $standings['drivers'] as $idx => $row ) {
					if ( (string) $row['car'] === $car ) { $rank = (int) $row['position']; break; }
				}
			}
			$has_photo = has_post_thumbnail( $d->ID );
			$rank_class = $rank === 1 ? 'p1' : '';
		?>
			<a href="<?php echo esc_url( get_permalink( $d->ID ) ); ?>" class="driver-tile">
				<?php if ( $rank ) : ?>
					<span class="rank-chip <?php echo esc_attr( $rank_class ); ?>">P<?php echo (int) $rank; ?></span>
				<?php endif; ?>
				<span class="car-chip">#<?php echo esc_html( $car ); ?></span>
				<div class="photo">
					<?php if ( $has_photo ) : ?>
						<?php echo get_the_post_thumbnail( $d->ID, 'vmra-card', array( 'alt' => esc_attr( get_the_title( $d->ID ) . ' · Car #' . $car ) ) ); ?>
					<?php else : ?>
						<div class="car-plate-big"><?php echo esc_html( $car ); ?></div>
					<?php endif; ?>
				</div>
				<div class="body">
					<h3><?php echo esc_html( get_the_title( $d->ID ) ); ?></h3>
					<div class="meta">
						<span><?php echo $home ? esc_html( $home ) : '&mdash;'; ?></span>
						<?php if ( $pts !== null ) : ?><span class="pts"><?php echo (int) $pts; ?> pts</span><?php endif; ?>
					</div>
					<?php if ( $defending || $rookie || $titles > 0 ) : ?>
						<div class="badges">
							<?php if ( $defending ) : ?><span class="badge champ">Defending</span><?php endif; ?>
							<?php if ( $rookie ) : ?><span class="badge rookie">Rookie</span><?php endif; ?>
							<?php if ( $titles > 0 ) : ?><span class="badge"><?php echo (int) $titles; ?>× Champ</span><?php endif; ?>
						</div>
					<?php endif; ?>
				</div>
			</a>
		<?php $i++; endforeach; ?>
	</div>

	<p class="legend">
		<?php printf( esc_html__( '%d drivers · 2026 YTD points shown next to each card · click a driver for full stats + round-by-round results', 'vmra' ), count( $drivers ) ); ?>
	</p>
</main>

<?php get_footer();
