<?php
/**
 * Homepage template.
 *
 * Phase 1 scope: hero, next-race panel, standings card, news grid.
 * Remaining static-site sections (full driver roster, tracks grid,
 * FAQ accordion, downloads row) are deferred to Phase 2 templates.
 *
 * Data sources:
 *   - Standings : vmra_race CPT rollup (future); falls back to /data/standings.json seed for now.
 *   - News      : vmra_news CPT query;          falls back to /data/news.json seed.
 *   - Next race : vmra_race CPT query;          falls back to /data/schedule.json seed.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// --- Pull seed JSON (fallback until CPT data is migrated) ---
$standings = vmra_seed_data( 'standings' );
$news      = vmra_seed_data( 'news' );
$schedule  = vmra_seed_data( 'schedule' );

// Pick the first upcoming race from the schedule (round after rounds_completed).
$next_race = null;
if ( is_array( $schedule ) && ! empty( $schedule['races'] ) ) {
	$rounds_done = isset( $standings['rounds_completed'] ) ? (int) $standings['rounds_completed'] : 0;
	foreach ( $schedule['races'] as $r ) {
		if ( (int) ( $r['round'] ?? 0 ) > $rounds_done ) {
			$next_race = $r;
			break;
		}
	}
}
?>

<!-- ===== TICKER ===== -->
<div class="ticker" id="topTicker">
	<div class="ticker-track" id="tickerTrack">
		<span><?php esc_html_e( '40th Anniversary Season Underway', 'vmra' ); ?></span>
		<?php if ( $next_race ) : ?>
			<span class="ticker-next-race"><?php
				printf(
					/* translators: 1: date, 2: track, 3: event, 4: round, 5: total rounds */
					esc_html__( 'Next Round: %1$s · %2$s · %3$s (Round %4$02d / %5$d)', 'vmra' ),
					esc_html( $next_race['date_human'] ?? $next_race['date'] ?? '' ),
					esc_html( $next_race['track'] ?? '' ),
					esc_html( $next_race['event'] ?? '' ),
					(int) ( $next_race['round'] ?? 0 ),
					count( $schedule['races'] )
				);
			?></span>
		<?php endif; ?>
		<?php if ( ! empty( $standings['drivers'] ) ) : $leader = $standings['drivers'][0]; ?>
			<span><?php
				printf(
					/* translators: 1: driver name, 2: car number */
					esc_html__( 'Championship Points · %1$s #%2$s leads', 'vmra' ),
					esc_html( $leader['name'] ),
					esc_html( $leader['car'] )
				);
			?></span>
		<?php endif; ?>
	</div>
</div>

<!-- ===== HERO ===== -->
<section class="hero">
	<div class="hero-inner">
		<?php
		$hero_eyebrow = get_theme_mod( 'vmra_hero_eyebrow', __( '§ 40th Anniversary · 2026', 'vmra' ) );
		$hero_title   = get_theme_mod( 'vmra_hero_title',   __( 'Vintage Modified. Pacific Northwest. Forty Years.', 'vmra' ) );
		$hero_lede    = get_theme_mod( 'vmra_hero_lede',    __( 'American-made pre-1970 stock cars, small-block V8s, hand-built by the drivers. Eleven dates across five tracks in three states. One club carrying forty years of oval racing forward.', 'vmra' ) );
		?>
		<span class="eyebrow"><?php echo esc_html( $hero_eyebrow ); ?></span>
		<h1><?php echo esc_html( $hero_title ); ?></h1>
		<p class="lede"><?php echo esc_html( $hero_lede ); ?></p>
		<div class="hero-ctas">
			<a href="<?php echo esc_url( home_url( '/schedule/' ) ); ?>" class="cta cta-primary"><?php esc_html_e( 'See the Schedule', 'vmra' ); ?> <span class="arr">›</span></a>
			<a href="<?php echo esc_url( home_url( '/standings/' ) ); ?>" class="cta cta-secondary"><?php esc_html_e( 'Current Standings', 'vmra' ); ?> <span class="arr">›</span></a>
		</div>
	</div>
</section>

<!-- ===== STANDINGS CARD (top 5 after R01) ===== -->
<?php if ( ! empty( $standings['drivers'] ) ) : ?>
<section class="block" aria-label="<?php esc_attr_e( 'Championship standings', 'vmra' ); ?>">
	<div class="block-head">
		<div class="block-title">
			<span class="block-marker"><?php
				printf(
					esc_html__( '§ 01 / Championship · %s YTD', 'vmra' ),
					esc_html( $standings['season'] ?? '2026' )
				);
			?></span>
			<h2><?php
				$leader = $standings['drivers'][0];
				printf(
					esc_html__( '%s on Top After Round %02d', 'vmra' ),
					esc_html( explode( ' ', $leader['name'] )[0] ),
					(int) ( $standings['rounds_completed'] ?? 1 )
				);
			?></h2>
			<p class="block-sub"><?php esc_html_e( 'Top five by season points. Full book on the standings page.', 'vmra' ); ?></p>
		</div>
		<a href="<?php echo esc_url( home_url( '/standings/' ) ); ?>" class="block-link"><?php esc_html_e( 'Full Standings →', 'vmra' ); ?></a>
	</div>

	<div class="standings-wrap">
		<div class="standings">
			<div class="standings-row standings-head">
				<div><?php esc_html_e( 'Pos', 'vmra' ); ?></div>
				<div><?php esc_html_e( 'Car', 'vmra' ); ?></div>
				<div><?php esc_html_e( 'Driver', 'vmra' ); ?></div>
				<div class="hide-sm"><?php esc_html_e( 'Status', 'vmra' ); ?></div>
				<div style="text-align:right;"><?php esc_html_e( 'Points', 'vmra' ); ?></div>
			</div>
			<?php foreach ( array_slice( $standings['drivers'], 0, 5 ) as $i => $d ) :
				$pos_class = $i === 0 ? 'p1' : ( $i === 1 ? 'p2' : ( $i === 2 ? 'p3' : '' ) );
				$plate_class = $i === 0 ? 'leader' : '';
			?>
				<div class="standings-row">
					<div class="pos <?php echo esc_attr( $pos_class ); ?>"><?php printf( '%02d', (int) $d['position'] ); ?></div>
					<div><div class="car-plate <?php echo esc_attr( $plate_class ); ?>"><?php echo esc_html( $d['car'] ); ?></div></div>
					<div>
						<div class="driver-name"><?php echo esc_html( $d['name'] ); ?></div>
					</div>
					<div class="wins hide-sm">—</div>
					<div class="points"><?php echo esc_html( $d['points'] ); ?></div>
				</div>
			<?php endforeach; ?>
		</div>

		<aside class="season-panel">
			<h3><?php esc_html_e( '2026 Season at a Glance', 'vmra' ); ?></h3>
			<div class="stat-row">
				<span class="stat-k"><?php esc_html_e( 'Championship Rounds', 'vmra' ); ?></span>
				<span class="stat-v"><?php printf( '%02d / 09', (int) ( $standings['rounds_completed'] ?? 1 ) ); ?></span>
			</div>
			<div class="stat-row">
				<span class="stat-k"><?php esc_html_e( 'Total Events (incl. Specials)', 'vmra' ); ?></span>
				<span class="stat-v"><?php printf( '%02d / %02d', (int) ( $standings['rounds_completed'] ?? 1 ), count( $schedule['races'] ?? array() ) ); ?></span>
			</div>
			<div class="stat-row">
				<span class="stat-k"><?php esc_html_e( 'Active Drivers', 'vmra' ); ?></span>
				<span class="stat-v"><?php echo esc_html( count( $standings['drivers'] ) ); ?></span>
			</div>
			<div class="stat-row">
				<span class="stat-k"><?php esc_html_e( 'Tracks On Tour', 'vmra' ); ?></span>
				<span class="stat-v">5</span>
			</div>
		</aside>
	</div>
</section>
<?php endif; ?>

<!-- ===== NEWS GRID ===== -->
<?php if ( ! empty( $news['items'] ) ) : ?>
<section class="block">
	<div class="block-head">
		<div class="block-title">
			<span class="block-marker">§ 02 / <?php esc_html_e( 'Latest', 'vmra' ); ?></span>
			<h2><?php esc_html_e( 'From the Paddock', 'vmra' ); ?></h2>
		</div>
		<a href="<?php echo esc_url( home_url( '/news/' ) ); ?>" class="block-link"><?php esc_html_e( 'All News →', 'vmra' ); ?></a>
	</div>

	<div class="news-grid">
		<?php foreach ( array_slice( $news['items'], 0, 4 ) as $i => $n ) :
			$is_feature = ! empty( $n['feature'] ) && $i === 0;
			$class      = 'news-card' . ( $is_feature ? ' feature' : '' );
			$link       = ! empty( $n['link'] ) ? home_url( $n['link'] ) : home_url( '/news/' );
		?>
			<a href="<?php echo esc_url( $link ); ?>" class="<?php echo esc_attr( $class ); ?>">
				<?php if ( $is_feature && ! empty( $n['car_num'] ) ) : ?>
					<div class="news-photo"><div class="num"><?php echo esc_html( $n['car_num'] ); ?></div></div>
				<?php endif; ?>
				<div class="news-tag"><?php echo esc_html( $n['category'] ?? __( 'News', 'vmra' ) ); ?></div>
				<h3 class="news-headline"><?php echo esc_html( $n['headline'] ); ?></h3>
				<p class="news-snippet"><?php echo esc_html( $n['snippet'] ); ?></p>
				<div class="news-meta">
					<span><?php echo esc_html( $n['date'] ?? '' ); ?><?php if ( ! empty( $n['byline'] ) ) : ?> · by <strong class="byline-strong"><?php echo esc_html( $n['byline'] ); ?></strong><?php endif; ?></span>
					<span class="arrow">→</span>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- ===== 40TH MILESTONE STRIP ===== -->
<section class="milestone">
	<div class="milestone-inner">
		<div class="milestone-40">40</div>
		<div class="milestone-copy">
			<h3><?php esc_html_e( 'Four Decades of the Pacific Northwest\'s Vintage Modifieds', 'vmra' ); ?></h3>
			<p><?php esc_html_e( 'From Spanaway Speedway in \'86 to Evergreen, Tri-City, Wenatchee, South Sound, and Stateline today — explore forty years of champions, cars, and stories.', 'vmra' ); ?></p>
		</div>
		<a href="<?php echo esc_url( home_url( '/standings/' ) ); ?>" class="milestone-btn"><?php esc_html_e( 'Current Standings →', 'vmra' ); ?></a>
	</div>
</section>

<?php
get_footer();
