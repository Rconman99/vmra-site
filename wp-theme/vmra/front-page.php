<?php
/**
 * Homepage template — full parity with the Vercel static index.html.
 *
 * Section order mirrors the static site:
 *   1. Ticker
 *   2. Hero (left: copy / right: live race panel + countdown)
 *   3. About strip (AEO answer-first)
 *   4. Upcoming races strip
 *   5. Cars block (Vince #72 video + heritage diptych)
 *   6. Preview strip (What To Watch)
 *   7. Standings card (top 5 + season panel)
 *   8. 40th milestone
 *   9. News grid
 *  10. Drivers roster (top 4)
 *  11. Tracks grid (6 cards)
 *  12. Downloads
 *  13. FAQ (matches FAQPage JSON-LD)
 *  14. Sponsor wall
 *  15. Subscribe strip
 *
 * Data sources for JS hydration:
 *   Schedule, standings, results, news JSON are shipped in /wp-content/themes/vmra/data/.
 *   Each <script> below fetches those files directly so the client-side
 *   ticker/countdown/hydration logic from the static site runs unchanged.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Pre-rendered fallback content from the seed JSON (rendered at first paint
// so the page has real content even if JS is disabled).
$standings = vmra_seed_data( 'standings' );
$news      = vmra_seed_data( 'news' );
$schedule  = vmra_seed_data( 'schedule' );

// URL base for JSON data fetched by the client-side scripts below.
$data_base = VMRA_THEME_URI . '/data';

get_header(); ?>

<!-- ===================== TICKER ===================== -->
<div class="ticker" id="topTicker">
	<div class="ticker-track" id="tickerTrack">
		<span>40th Anniversary Season Underway</span>
		<span class="ticker-next-race">Next Round: Sat Apr 25 · Evergreen · Grocery Outlet Night (Round 02 / 11)</span>
		<span>Championship Points · <?php echo ! empty( $standings['drivers'] ) ? esc_html( $standings['drivers'][0]['name'] . ' #' . $standings['drivers'][0]['car'] ) : 'TBD'; ?> leads</span>
		<span>Fall Classic Entries Open</span>
		<span>New Rulebook PDF Available</span>
		<span>Sponsor Slots Open for 2026</span>
		<span>40th Anniversary Season Underway</span>
		<span class="ticker-next-race">Next Round: Sat Apr 25 · Evergreen · Grocery Outlet Night (Round 02 / 11)</span>
	</div>
</div>
<script>
/* Ticker auto-updates "Next Round" items from schedule.json. */
(function(){
	function fmtRace(race) {
		var d = new Date(race.date + "T12:00:00-07:00");
		var raceDay = new Date(d); raceDay.setHours(0,0,0,0);
		var today = new Date(); today.setHours(0,0,0,0);
		var daysOut = Math.round((raceDay - today) / 86400000);
		var dayName = ["Sun","Mon","Tue","Wed","Thu","Fri","Sat"][d.getDay()];
		var monthName = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"][d.getMonth()];
		var dateStr = dayName + " " + monthName + " " + d.getDate();
		if (daysOut < 0)   return "Next Round: " + race.short + " · " + dateStr;
		if (daysOut === 0) return "RACE DAY · " + race.short;
		if (daysOut === 1) return "TOMORROW · " + race.short;
		if (daysOut <= 3)  return "RACE WEEKEND · " + race.short + " · " + dateStr;
		if (daysOut <= 14) return "Next Round: " + race.short + " · " + dateStr + " · " + daysOut + " days out";
		return "Next Round: " + race.short + " · " + dateStr;
	}
	function pickNext(races) {
		var today = new Date(); today.setHours(0,0,0,0);
		for (var i = 0; i < races.length; i++) {
			var rd = new Date(races[i].date + "T12:00:00-07:00"); rd.setHours(0,0,0,0);
			if (rd >= today) return races[i];
		}
		return null;
	}
	function setTicker(text) {
		document.querySelectorAll(".ticker-next-race").forEach(function(el){ el.textContent = text; });
	}
	fetch('<?php echo esc_url( $data_base . '/schedule.json' ); ?>')
		.then(function(r){ return r.json(); })
		.then(function(data){
			var next = pickNext(data.races);
			setTicker(next ? fmtRace(next) : data.season + " Season Complete · " + (parseInt(data.season)+1) + " Calendar TBD");
		})
		.catch(function(){ setTicker("Next Round: see Schedule page for upcoming dates"); });
})();
</script>

<!-- ===================== HERO ===================== -->
<header class="hero" id="top">
	<div class="hero-marks" aria-hidden="true">
		<span class="num l">40</span>
		<span class="num r">VM</span>
		<span class="yr">1986—2026</span>
	</div>
	<div class="hero-grain" aria-hidden="true"></div>
	<div class="hero-vignette" aria-hidden="true"></div>

	<div class="hero-inner">
		<div class="hero-copy">
			<div class="badge">
				<span class="pulse" aria-hidden="true"></span>
				<span class="ix">§ 040</span>
				<span><?php esc_html_e( '2026 Season · 40th Anniversary', 'vmra' ); ?></span>
			</div>
			<h1>
				<span class="ln1"><?php esc_html_e( 'Forty Years.', 'vmra' ); ?></span>
				<span class="ln2"><?php esc_html_e( 'Still Modified', 'vmra' ); ?><span class="dot" aria-hidden="true"></span></span>
			</h1>
			<div class="hero-rule" aria-hidden="true"></div>
			<p class="hero-lede">
				<?php esc_html_e( 'Vintage modifieds running the tracks of the Pacific Northwest since 1986. Real cars. Real drivers. Real dirt — kept alive by the crews who refuse to let it die.', 'vmra' ); ?>
			</p>
			<div class="hero-actions">
				<a href="<?php echo esc_url( home_url( '/schedule/' ) ); ?>" class="hero-link primary"><?php esc_html_e( '2026 Schedule', 'vmra' ); ?> <span class="arr">→</span></a>
				<a href="<?php echo esc_url( home_url( '/racers/' ) ); ?>" class="hero-link"><?php esc_html_e( 'Meet the Drivers', 'vmra' ); ?> <span class="arr">→</span></a>
			</div>
		</div>

		<aside class="race-panel" id="countdownTower" aria-label="<?php esc_attr_e( 'Next round race control panel', 'vmra' ); ?>">
			<div class="panel-strip">
				<span><?php esc_html_e( 'Race Control', 'vmra' ); ?></span>
				<span class="live"><?php esc_html_e( 'Live', 'vmra' ); ?></span>
			</div>
			<div class="panel-body">
				<div class="panel-head">
					<div>
						<div class="label"><?php esc_html_e( 'Next Round', 'vmra' ); ?></div>
						<div class="round" id="towerRound">Round 02 / 11</div>
					</div>
					<span class="tag">Hoosier · ST1 / ST2 / ST3</span>
				</div>
				<div class="panel-track" id="towerTrack">Evergreen Speedway</div>
				<div class="panel-loc" id="towerLocation">Evergreen Speedway · Monroe, WA · The Half-Mile</div>

				<div class="countdown" aria-live="polite">
					<div class="count-cell"><div class="count-num">--</div><div class="count-label"><?php esc_html_e( 'Days', 'vmra' ); ?></div></div>
					<div class="count-cell"><div class="count-num">--</div><div class="count-label"><?php esc_html_e( 'Hrs',  'vmra' ); ?></div></div>
					<div class="count-cell"><div class="count-num">--</div><div class="count-label"><?php esc_html_e( 'Min',  'vmra' ); ?></div></div>
					<div class="count-cell"><div class="count-num">--</div><div class="count-label"><?php esc_html_e( 'Sec',  'vmra' ); ?></div></div>
				</div>

				<div class="panel-meta">
					<div><span class="k"><?php esc_html_e( 'Green Flag', 'vmra' ); ?></span><span class="v" id="towerGreenFlag">Sat Apr 25 · 7:30 PM</span></div>
					<div><span class="k"><?php esc_html_e( 'Event', 'vmra' ); ?></span><span class="v" id="towerEvent">Grocery Outlet Night</span></div>
					<div><span class="k"><?php esc_html_e( 'Distance', 'vmra' ); ?></span><span class="v" id="towerDistance">5/8-mile paved oval — "The Half-Mile"</span></div>
					<div id="towerCarsRow"><span class="k"><?php esc_html_e( 'Confirmed Cars', 'vmra' ); ?></span><span class="v" id="towerCars">17 and counting</span></div>
				</div>
			</div>
		</aside>
	</div>
</header>

<!-- ===================== ABOUT STRIP (AEO) ===================== -->
<section class="about-strip" aria-label="<?php esc_attr_e( 'About the Vintage Modified Racing Association', 'vmra' ); ?>">
	<div class="about-inner">
		<div class="about-label"><?php esc_html_e( 'About VMRA', 'vmra' ); ?><br><?php esc_html_e( 'Est. 1986 · Pacific Northwest', 'vmra' ); ?></div>
		<div class="about-copy">
			<p><strong><?php esc_html_e( 'The Vintage Modified Racing Association (VMRA)', 'vmra' ); ?></strong> <?php esc_html_e( 'is a non-profit Pacific Northwest racing club founded in 1986 at Spanaway Speedway, Washington. We organize vintage modified circle-track races across Washington, Oregon, and Idaho — preserving the spirit of 1950s through early 1970s modified stock car racing through a rulebook built for authenticity and integrity.', 'vmra' ); ?></p>
			<p><?php esc_html_e( 'The', 'vmra' ); ?> <strong><?php esc_html_e( '2026 season is our 40th Anniversary', 'vmra' ); ?></strong><?php esc_html_e( ', running at Evergreen Speedway (Monroe WA), Tri-City Raceway (West Richland WA), Wenatchee Valley Super Oval (East Wenatchee WA), South Sound Speedway (Rochester WA), and Stateline Speedway (Post Falls ID).', 'vmra' ); ?></p>
			<div class="about-meta">
				<div><span class="k"><?php esc_html_e( 'Founded', 'vmra' ); ?></span><span class="v">1986 · Spanaway WA</span></div>
				<div><span class="k"><?php esc_html_e( 'Sport', 'vmra' ); ?></span><span class="v">Vintage Modified Circle Track</span></div>
				<div><span class="k"><?php esc_html_e( 'Active Drivers', 'vmra' ); ?></span><span class="v"><?php echo (int) count( $standings['drivers'] ?? array() ); ?> on the 2026 grid</span></div>
				<div><span class="k"><?php esc_html_e( 'Tracks', 'vmra' ); ?></span><span class="v">WA · OR · ID</span></div>
				<div><span class="k"><?php esc_html_e( 'Tire Spec', 'vmra' ); ?></span><span class="v">Hoosier ST1 / ST2 / ST3</span></div>
			</div>
		</div>
	</div>
</section>

<!-- ===================== UPCOMING RACES STRIP ===================== -->
<section class="upcoming-strip" aria-label="<?php esc_attr_e( 'Upcoming VMRA races', 'vmra' ); ?>">
	<div class="upcoming-inner">
		<div class="upcoming-head">
			<div class="titleblock">
				<span class="marker"><?php esc_html_e( '§ Next 4 Rounds', 'vmra' ); ?></span>
				<h2><?php esc_html_e( '2026 Season · Upcoming Races', 'vmra' ); ?></h2>
			</div>
			<a href="<?php echo esc_url( home_url( '/schedule/' ) ); ?>" class="link"><?php esc_html_e( 'Full Schedule →', 'vmra' ); ?></a>
		</div>
		<div class="upcoming-grid" id="upcomingGrid">
			<article class="race-card next">
				<div class="date"><span class="month">Apr</span><span class="day">25</span></div>
				<div class="round">Round 02 / 11</div>
				<h3>Grocery Outlet Night</h3>
				<div class="venue"><strong>Evergreen Speedway</strong><br>Evergreen Speedway · Monroe, WA · The Half-Mile<span class="addr">14405 179th Ave SE, Monroe, WA 98272</span></div>
				<div class="meta"><span class="purse">Hoosier · ST1 / ST2</span><a class="arrow" href="https://www.google.com/maps/dir/?api=1&amp;destination=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272" target="_blank" rel="noopener">Directions →</a></div>
			</article>
			<article class="race-card">
				<div class="date"><span class="month">May</span><span class="day">02</span></div>
				<div class="round">Round 03 / 11</div>
				<h3>Apple Blossom Rubber Down</h3>
				<div class="venue"><strong>Wenatchee Valley Super Oval</strong><br>Wenatchee Valley Super Oval · East Wenatchee, WA<span class="addr">2850 Gun Club Rd, East Wenatchee, WA 98802</span></div>
				<div class="meta"><span class="purse">Hoosier · ST1 / ST2</span><a class="arrow" href="https://www.google.com/maps/dir/?api=1&amp;destination=2850%20Gun%20Club%20Rd%2C%20East%20Wenatchee%2C%20WA%2098802" target="_blank" rel="noopener">Directions →</a></div>
			</article>
			<article class="race-card">
				<div class="date"><span class="month">Jun</span><span class="day">27</span></div>
				<div class="round">Round 04 / 11</div>
				<h3>CARS Tour Mark Galloway Shootout</h3>
				<div class="venue"><strong>Evergreen Speedway</strong><br>Evergreen Speedway · Monroe, WA · The Half-Mile<span class="addr">14405 179th Ave SE, Monroe, WA 98272</span></div>
				<div class="meta"><span class="purse">Hoosier · ST1 / ST2</span><a class="arrow" href="https://www.google.com/maps/dir/?api=1&amp;destination=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272" target="_blank" rel="noopener">Directions →</a></div>
			</article>
			<article class="race-card">
				<div class="date"><span class="month">Jul</span><span class="day">11</span></div>
				<div class="round">Special · Non-Points</div>
				<h3>Ron Rohde Memorial</h3>
				<div class="venue"><strong>Stateline Speedway</strong><br>Stateline Speedway · Post Falls, ID<span class="addr">1349 N Beck Rd, Post Falls, ID 83854</span></div>
				<div class="meta"><span class="purse">Non-points</span><a class="arrow" href="https://www.google.com/maps/dir/?api=1&amp;destination=1349%20N%20Beck%20Rd%2C%20Post%20Falls%2C%20ID%2083854" target="_blank" rel="noopener">Directions →</a></div>
			</article>
		</div>
		<script>
		(function(){
			var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
			function escapeHtml(s){ return String(s).replace(/[&<>"]/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;'}[c]; }); }
			fetch('<?php echo esc_url( $data_base . '/schedule.json' ); ?>').then(function(r){ return r.json(); }).then(function(data){
				var today = new Date(); today.setHours(0,0,0,0);
				var totalRounds = data.races.length;
				var upcoming = data.races.filter(function(race){
					var rd = new Date(race.date + 'T12:00:00-07:00'); rd.setHours(0,0,0,0);
					return rd >= today;
				}).slice(0, 4);
				if (upcoming.length === 0) {
					document.getElementById('upcomingGrid').innerHTML =
						'<div style="grid-column:1/-1;text-align:center;color:var(--chalk-dim);padding:30px">Season complete. See you in 2027.</div>';
					return;
				}
				var tracks = data.tracks || {};
				var html = upcoming.map(function(race, idx){
					var d = new Date(race.date + 'T12:00:00-07:00');
					var roundLabel = race.round === 'TBD' ? 'TBD' :
						(race.tag === 'exhibition' ? 'Special · Non-Points' : 'Round ' + String(race.round).padStart(2,'0') + ' / ' + totalRounds);
					var purseText = race.tag === 'exhibition' ? 'Non-points' :
						(race.tag === 'feature' ? 'Championship Night' : 'Hoosier · ST1 / ST2');
					var nextClass = idx === 0 ? ' next' : '';
					var info = tracks[race.track] || {};
					var addr = info.address || '';
					var addrLine = addr ? '<span class="addr">' + escapeHtml(addr) + '</span>' : '';
					var dirHref = addr ? 'https://www.google.com/maps/dir/?api=1&destination=' + encodeURIComponent(addr) : '';
					var dirLink = dirHref
						? '<a class="arrow" href="' + dirHref + '" target="_blank" rel="noopener">Directions →</a>'
						: '<span class="arrow">Info →</span>';
					return '<article class="race-card' + nextClass + '">' +
						'<div class="date"><span class="month">' + months[d.getMonth()] + '</span><span class="day">' + String(d.getDate()).padStart(2,'0') + '</span></div>' +
						'<div class="round">' + roundLabel + '</div>' +
						'<h3>' + escapeHtml(race.event_name || race.tag_label) + '</h3>' +
						'<div class="venue"><strong>' + escapeHtml(race.track) + '</strong><br>' + escapeHtml(race.location) + addrLine + '</div>' +
						'<div class="meta"><span class="purse">' + purseText + '</span>' + dirLink + '</div>' +
						'</article>';
				}).join('');
				document.getElementById('upcomingGrid').innerHTML = html;
			}).catch(function(){
				document.getElementById('upcomingGrid').innerHTML =
					'<div style="grid-column:1/-1;text-align:center;color:var(--chalk-dim);padding:30px">Schedule temporarily unavailable.</div>';
			});
		})();
		</script>
	</div>
</section>

<!-- ===================== PREVIEW · What To Watch ===================== -->
<section class="preview-strip" aria-label="<?php esc_attr_e( 'Pre-race preview', 'vmra' ); ?>">
	<div class="preview-inner">
		<div class="preview-label">
			<span class="sub-label">§ Pre-Race · Round 02</span>
			What To <span class="accent">Watch For</span><br>at Evergreen
		</div>
		<article class="preview-article">
			<h3>Cheth Rolls Into Evergreen as the Points Leader — the Chasers Have Some Math to Do</h3>
			<p>Kahl Cheth took the Apple Cup opener at Tri-City and walked out 64 points rich with the #23 on top of the board. Saturday he heads to Evergreen's half-mile paved oval for Grocery Outlet Night — where <strong>Steve Woods has historically found speed</strong>. Woods sits third, seven back, and if there's a track where he closes the gap, this is it. <strong>Jason Quatsoe #8</strong> is the one between them — four off Cheth, three up on Woods — and Evergreen's a track he can run.</p>
			<p>The big subplot: defending champ <strong>Kyten Jones #30</strong> never unloaded at Tri-City. He sits on zero points heading into Round 2 on his home track. Every round he waits, the hill gets steeper — but Evergreen has been a Jones track. If he shows up, he's the one to beat Saturday night.</p>
			<p>Rookie-of-the-Year watch: <strong>B. Hector Sr #68</strong> is the early class leader at 35 rookie points, with <strong>C. Forney #72</strong> (29) and <strong>J. Boczar #79</strong> (12) chasing.</p>
			<p class="preview-byline">By <span class="byline-strong">The VMRA Desk</span> · April 23, 2026 · <a href="<?php echo esc_url( home_url( '/news/class-of-2026' ) ); ?>" style="color: var(--race-red); text-decoration: none;">Full preview →</a></p>
		</article>
	</div>
</section>

<!-- ===================== STANDINGS ===================== -->
<?php if ( ! empty( $standings['drivers'] ) ) : ?>
<section class="block" aria-label="<?php esc_attr_e( '2026 championship standings — top five', 'vmra' ); ?>">
	<div class="block-head">
		<div class="block-title">
			<span class="block-marker"><?php printf( esc_html__( '§ 01 / Championship · %s YTD', 'vmra' ), esc_html( $standings['season'] ?? '2026' ) ); ?></span>
			<h2><?php
				$leader = $standings['drivers'][0];
				$leader_first = explode( ' ', $leader['name'] )[0];
				printf( esc_html__( '%s on Top After Round %02d', 'vmra' ), esc_html( $leader_first ), (int) ( $standings['rounds_completed'] ?? 1 ) );
			?></h2>
			<p class="block-sub"><?php esc_html_e( 'Top five after the Apple Cup opener at Tri-City. Updates after every round.', 'vmra' ); ?></p>
		</div>
		<a href="<?php echo esc_url( home_url( '/standings/' ) ); ?>" class="block-link"><?php esc_html_e( 'Full Standings →', 'vmra' ); ?></a>
	</div>

	<div class="standings-wrap">
		<div class="standings">
			<div class="standings-row standings-head">
				<div>Pos</div>
				<div>Car</div>
				<div>Driver</div>
				<div class="hide-sm">R1 Finish</div>
				<div style="text-align:right;">Points</div>
			</div>
			<?php
			$finish_labels = array( 'Main Win', 'P2 Main', 'P3 Main', 'P4 Main', 'P5 Main' );
			foreach ( array_slice( $standings['drivers'], 0, 5 ) as $i => $d ) :
				$pos_class   = $i === 0 ? 'p1' : ( $i === 1 ? 'p2' : ( $i === 2 ? 'p3' : '' ) );
				$plate_class = $i === 0 ? 'leader' : '';
			?>
				<div class="standings-row">
					<div class="pos <?php echo esc_attr( $pos_class ); ?>"><?php printf( '%02d', (int) $d['position'] ); ?></div>
					<div><div class="car-plate <?php echo esc_attr( $plate_class ); ?>"><?php echo esc_html( $d['car'] ); ?></div></div>
					<div>
						<div class="driver-name"><?php echo esc_html( $d['name'] ); ?></div>
						<span class="driver-town"><?php echo $i === 0 ? 'Apple Cup Winner · WA' : ( $i === 1 ? 'Second · WA' : ( $i === 2 ? '3x Champion · Wenatchee WA' : ( $i === 3 ? 'Rookie · R1 Top-5' : 'Strimple Racing · WA' ) ) ); ?></span>
					</div>
					<div class="wins hide-sm"><?php echo esc_html( $finish_labels[ $i ] ?? '—' ); ?></div>
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
			<div class="stat-row">
				<span class="stat-k"><?php esc_html_e( 'Fast Time (YTD)', 'vmra' ); ?></span>
				<span class="stat-v">16.02s</span>
			</div>
		</aside>
	</div>
</section>
<?php endif; ?>

<!-- ===================== 40TH MILESTONE ===================== -->
<section class="milestone">
	<div class="milestone-inner">
		<div class="milestone-40">40</div>
		<div class="milestone-copy">
			<h3><?php esc_html_e( "Four Decades of the Pacific Northwest's Vintage Modifieds", 'vmra' ); ?></h3>
			<p><?php esc_html_e( "From Spanaway Speedway in '86 to Evergreen, Tri-City, Wenatchee, South Sound, and Stateline today — explore forty years of champions, cars, and stories. The 40th Anniversary Archive is live.", 'vmra' ); ?></p>
		</div>
		<a href="<?php echo esc_url( home_url( '/standings/' ) ); ?>" class="milestone-btn"><?php esc_html_e( 'Current Standings →', 'vmra' ); ?></a>
	</div>
</section>

<!-- ===================== NEWS GRID ===================== -->
<?php if ( ! empty( $news['items'] ) ) : ?>
<section class="block">
	<div class="block-head">
		<div class="block-title">
			<span class="block-marker">§ 02 / <?php esc_html_e( 'Latest', 'vmra' ); ?></span>
			<h2><?php esc_html_e( 'From the Paddock', 'vmra' ); ?></h2>
		</div>
		<a href="<?php echo esc_url( home_url( '/news/class-of-2026' ) ); ?>" class="block-link"><?php esc_html_e( 'Read the Latest Recap →', 'vmra' ); ?></a>
	</div>

	<div class="news-grid" id="newsGrid">
		<?php foreach ( array_slice( $news['items'], 0, 5 ) as $i => $n ) :
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
					<span class="arrow"><?php echo $is_feature ? 'Read Full Recap →' : '→'; ?></span>
				</div>
			</a>
		<?php endforeach; ?>
	</div>
</section>
<?php endif; ?>

<!-- ===================== DRIVERS ROSTER ===================== -->
<section class="block">
	<div class="block-head">
		<div class="block-title">
			<span class="block-marker"><?php esc_html_e( '§ 03 / Grid', 'vmra' ); ?></span>
			<h2><?php esc_html_e( 'The 2026 Roster', 'vmra' ); ?></h2>
		</div>
		<a href="<?php echo esc_url( home_url( '/racers/' ) ); ?>" class="block-link"><?php esc_html_e( 'All VMRA Drivers & Profiles →', 'vmra' ); ?></a>
	</div>

	<div class="roster">
		<?php
		$roster_meta = array(
			array( 'town' => 'Tacoma, WA',     'rank' => 'P1' ),
			array( 'town' => 'Washington',     'rank' => 'P2' ),
			array( 'town' => 'Wenatchee, WA',  'rank' => 'P3' ),
			array( 'town' => 'Rookie',         'rank' => 'P4' ),
		);
		foreach ( array_slice( $standings['drivers'] ?? array(), 0, 4 ) as $i => $d ) :
		?>
			<div class="driver-card">
				<div class="driver-head"><div class="driver-num"><?php echo esc_html( $d['car'] ); ?></div></div>
				<div class="driver-body">
					<h4><?php echo esc_html( $d['name'] ); ?></h4>
					<div class="meta"><span><?php echo esc_html( $roster_meta[ $i ]['town'] ); ?></span><span class="rank"><?php echo esc_html( $roster_meta[ $i ]['rank'] ); ?></span></div>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
	<p style="font-family:'JetBrains Mono',monospace;font-size:.78rem;letter-spacing:.12em;color:var(--chalk-dim);text-transform:uppercase;text-align:center;margin-top:22px">Top of the 2026 book after Round 01 · <a href="<?php echo esc_url( home_url( '/racers/' ) ); ?>" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid var(--race-red);padding-bottom:2px">See all 23 drivers →</a></p>
</section>

<!-- ===================== TRACKS GRID ===================== -->
<section class="block" aria-label="<?php esc_attr_e( 'VMRA sanctioned tracks', 'vmra' ); ?>">
	<div class="block-head">
		<div class="block-title">
			<span class="block-marker"><?php esc_html_e( '§ 04 / Paddock', 'vmra' ); ?></span>
			<h2><?php esc_html_e( 'The Tracks We Race', 'vmra' ); ?></h2>
		</div>
		<a href="<?php echo esc_url( home_url( '/tracks/' ) ); ?>" class="block-link"><?php esc_html_e( 'All Track Profiles →', 'vmra' ); ?></a>
	</div>

	<div class="tracks-grid">
		<a href="<?php echo esc_url( home_url( '/tracks/' ) ); ?>" class="track-card">
			<div class="tc-state">Monroe · WA</div>
			<h4>Evergreen Speedway</h4>
			<div class="tc-city">The Fast Half-Mile</div>
			<div class="tc-meta">
				<div><span class="k">Surface</span> Paved oval</div>
				<div><span class="k">Length</span> ⅝ mile</div>
				<div><span class="k">2026 Rounds</span> 4</div>
			</div>
			<div class="tc-next">Next: Apr 25 →</div>
		</a>
		<a href="<?php echo esc_url( home_url( '/tracks/' ) ); ?>" class="track-card">
			<div class="tc-state">West Richland · WA</div>
			<h4>Tri-City Raceway</h4>
			<div class="tc-city">Home of the Apple Cup</div>
			<div class="tc-meta">
				<div><span class="k">Surface</span> Paved oval</div>
				<div><span class="k">Length</span> ⅜ mile</div>
				<div><span class="k">2026 Rounds</span> 3</div>
			</div>
			<div class="tc-next">Next: Oct 3 →</div>
		</a>
		<a href="<?php echo esc_url( home_url( '/tracks/' ) ); ?>" class="track-card">
			<div class="tc-state">East Wenatchee · WA</div>
			<h4>Wenatchee Valley Super Oval</h4>
			<div class="tc-city">Apple Country Short Track</div>
			<div class="tc-meta">
				<div><span class="k">Surface</span> Paved oval</div>
				<div><span class="k">Length</span> ¼ mile</div>
				<div><span class="k">2026 Rounds</span> 2</div>
			</div>
			<div class="tc-next">Next: May 2 →</div>
		</a>
		<a href="<?php echo esc_url( home_url( '/tracks/' ) ); ?>" class="track-card">
			<div class="tc-state">Rochester · WA</div>
			<h4>South Sound Speedway</h4>
			<div class="tc-city">40th Anniversary Bash Host</div>
			<div class="tc-meta">
				<div><span class="k">Surface</span> Paved oval + figure-8</div>
				<div><span class="k">Length</span> ⅜ mile</div>
				<div><span class="k">2026 Rounds</span> 1</div>
			</div>
			<div class="tc-next">Next: Jul 25 →</div>
		</a>
		<a href="<?php echo esc_url( home_url( '/tracks/' ) ); ?>" class="track-card">
			<div class="tc-state">Post Falls · ID</div>
			<h4>Stateline Speedway</h4>
			<div class="tc-city">Ron Rohde Memorial Host</div>
			<div class="tc-meta">
				<div><span class="k">Surface</span> Banked paved oval</div>
				<div><span class="k">Length</span> ¼ mile</div>
				<div><span class="k">2026 Rounds</span> 1 (non-points)</div>
			</div>
			<div class="tc-next">Next: Jul 11 →</div>
		</a>
		<a href="<?php echo esc_url( home_url( '/tracks/' ) ); ?>" class="track-card">
			<div class="tc-state">Spanaway · WA</div>
			<h4>Spanaway Speedway</h4>
			<div class="tc-city">Founding Track · 1986</div>
			<div class="tc-meta">
				<div><span class="k">Surface</span> Paved oval</div>
				<div><span class="k">Status</span> Legacy · Not on 2026 Schedule</div>
				<div><span class="k">Significance</span> Where VMRA began</div>
			</div>
			<div class="tc-next">Club History →</div>
		</a>
	</div>
</section>

<!-- ===================== DOWNLOADS ===================== -->
<section class="block" aria-label="<?php esc_attr_e( 'Downloads and resources', 'vmra' ); ?>">
	<div class="block-head">
		<div class="block-title">
			<span class="block-marker"><?php esc_html_e( '§ 05 / Resources', 'vmra' ); ?></span>
			<h2><?php esc_html_e( 'Downloads & Forms', 'vmra' ); ?></h2>
		</div>
		<a href="<?php echo esc_url( home_url( '/rules/' ) ); ?>" class="block-link"><?php esc_html_e( 'All Downloads →', 'vmra' ); ?></a>
	</div>

	<div class="downloads-row">
		<a href="<?php echo esc_url( home_url( '/rules/' ) ); ?>" class="dl-cell">
			<div>
				<div class="dl-icon">PDF</div>
				<h4><?php esc_html_e( '2026 VMRA Rulebook', 'vmra' ); ?></h4>
				<p class="dl-sub"><?php esc_html_e( 'The complete rulebook. Print a copy for the toolbox.', 'vmra' ); ?></p>
			</div>
			<div class="dl-action"><?php esc_html_e( 'Download ↓', 'vmra' ); ?></div>
		</a>
		<a href="<?php echo esc_url( VMRA_THEME_URI . '/assets/downloads/vmra-2026-membership-form.pdf' ); ?>" target="_blank" rel="noopener" class="dl-cell">
			<div>
				<div class="dl-icon">PDF</div>
				<h4><?php esc_html_e( '2026 Membership Application', 'vmra' ); ?></h4>
				<p class="dl-sub"><?php esc_html_e( '$50/year. Print, fill out, sign, bring to the next race. Required to score points.', 'vmra' ); ?></p>
			</div>
			<div class="dl-action"><?php esc_html_e( 'Download ↓', 'vmra' ); ?></div>
		</a>
	</div>
</section>

<!-- ===================== FAQ ===================== -->
<section class="block" aria-label="<?php esc_attr_e( 'Frequently asked questions', 'vmra' ); ?>">
	<div class="block-head">
		<div class="block-title">
			<span class="block-marker"><?php esc_html_e( '§ 06 / Answers', 'vmra' ); ?></span>
			<h2><?php esc_html_e( 'Questions, Answered', 'vmra' ); ?></h2>
		</div>
		<a href="<?php echo esc_url( home_url( '/rules/' ) ); ?>" class="block-link"><?php esc_html_e( 'Full Rulebook →', 'vmra' ); ?></a>
	</div>

	<div class="faq-wrap">
		<article class="faq-item">
			<h3><?php esc_html_e( 'What is the Vintage Modified Racing Association?', 'vmra' ); ?></h3>
			<p><?php esc_html_e( "VMRA is a non-profit racing club out of the Pacific Northwest. We started in 1986 at Spanaway Speedway with a small group of guys who wanted to keep 1950s through early '70s modified stock car racing alive. Forty years later we're still at it — running circle-track races at Evergreen, Tri-City, Wenatchee, South Sound, and Stateline. American-made pre-1970 cars (with a few specific exceptions), small-block V8s, fiberglass bodies, hand-built in members' shops.", 'vmra' ); ?></p>
		</article>
		<article class="faq-item">
			<h3><?php esc_html_e( 'How do I join VMRA or race a vintage modified?', 'vmra' ); ?></h3>
			<p>Annual membership is $50. Download the <a href="<?php echo esc_url( VMRA_THEME_URI . '/assets/downloads/vmra-2026-membership-form.pdf' ); ?>" target="_blank" rel="noopener">2026 Membership Application (PDF)</a>, print it, fill it out, sign it, and bring it to the next race weekend with cash or a check made out to VMRA. Or email the board at <a href="mailto:board@vmra.club">board@vmra.club</a> with questions. Your car needs to pass tech against the construction rules — pre-1970 American body, 2,950 lb minimum, 370 cubic inches max, no fuel injection, no blowers, no coil-overs. Tires are 10-inch-wide Hoosier take-offs (ST1 left, ST2/ST3 right), procured by VMRA.</p>
		</article>
		<article class="faq-item">
			<h3><?php esc_html_e( 'What type of cars race in VMRA?', 'vmra' ); ?></h3>
			<p>Vintage modified stock cars — the kind that ran in the '50s, '60s, and early '70s on dirt and pavement ovals across the country. Any American body 1969 or older, plus some 1970-77 compacts (Vega, Pinto, Gremlin) with tech approval. 370 cubic inches max, steel block only, flat-top pistons, carbureted only — no fuel injection, no blowers, no turbos, no nitrous, no alcohol. 2,950 lb minimum with 57% left-side max. Real cars built in real shops. Full spec is in the <a href="<?php echo esc_url( VMRA_THEME_URI . '/assets/downloads/vmra-construction-rules-2026-2028.docx' ); ?>">2026-2028 Construction Rules</a>.</p>
		</article>
	</div>
</section>

<!-- ===================== SPONSOR WALL ===================== -->
<section class="sponsor-block">
	<div class="sponsor-inner">
		<div class="sponsor-head">
			<div>
				<span class="block-marker"><?php esc_html_e( '§ 07 / Partners', 'vmra' ); ?></span>
				<h2><?php esc_html_e( 'Our', 'vmra' ); ?> <span class="accent"><?php esc_html_e( 'Paddock Partners', 'vmra' ); ?></span></h2>
			</div>
			<p class="sponsor-pitch">
				<?php esc_html_e( 'The businesses that keep the wheels turning. Every race weekend starts with them — at the tire trailer, on the contingency sheet, and on the car.', 'vmra' ); ?>
			</p>
		</div>

		<div class="sponsor-grid">
			<div class="sponsor-cell">
				<div class="tier"><?php esc_html_e( 'Official Tire of VMRA', 'vmra' ); ?></div>
				<div class="name">Pomp's Tire</div>
			</div>
			<div class="sponsor-cell">
				<div class="tier"><?php esc_html_e( 'Official Hosting Partner', 'vmra' ); ?></div>
				<div class="name">Big Mountain Mail</div>
			</div>
			<div class="sponsor-cell open">
				<div class="plus">+</div>
				<div class="name"><?php esc_html_e( 'Contingency Open', 'vmra' ); ?></div>
			</div>
			<div class="sponsor-cell open">
				<div class="plus">+</div>
				<div class="name"><?php esc_html_e( 'Class Sponsor Open', 'vmra' ); ?></div>
			</div>
			<div class="sponsor-cell open">
				<div class="plus">+</div>
				<div class="name"><?php esc_html_e( 'Event Sponsor Open', 'vmra' ); ?></div>
			</div>
		</div>

		<div class="sponsor-cta">
			<div>
				<h3><?php esc_html_e( 'Put Your Logo on the Fast Half-Mile', 'vmra' ); ?></h3>
				<p><?php esc_html_e( '908 Facebook followers. 23 drivers across 5 tracks. 40 years of brand equity. Download the 2026 Sponsorship Media Kit — tiers, pricing, reach, and why the 40th is the season to jump in.', 'vmra' ); ?></p>
			</div>
			<a href="mailto:board@vmra.club?subject=2026%20Sponsorship%20Media%20Kit%20Request" class="btn-media-kit"><?php esc_html_e( 'Request Media Kit →', 'vmra' ); ?></a>
		</div>
	</div>
</section>

<!-- ===================== SUBSCRIBE STRIP ===================== -->
<section class="subscribe-strip" id="subscribe" aria-label="<?php esc_attr_e( 'Subscribe to VMRA race updates', 'vmra' ); ?>">
	<div class="subscribe-inner">
		<div>
			<h2><?php esc_html_e( 'Race Day Alerts', 'vmra' ); ?><br><?php esc_html_e( 'Straight to Your Inbox', 'vmra' ); ?></h2>
			<p><?php esc_html_e( "Weekend recaps Monday morning. Next-round previews Thursday night. Rule changes the day they're posted. No spam, no ads, just racing.", 'vmra' ); ?></p>
		</div>
		<form class="sub-form" onsubmit="event.preventDefault(); var e=this.querySelector('input').value; if(e){window.location.href='mailto:board@vmra.club?subject=Subscribe%20to%20VMRA%20Updates&body=Please%20add%20'+encodeURIComponent(e)+'%20to%20the%20VMRA%20mailing%20list.';}">
			<input type="email" placeholder="<?php esc_attr_e( 'Your email address', 'vmra' ); ?>" aria-label="<?php esc_attr_e( 'Email address', 'vmra' ); ?>" required>
			<button type="submit"><?php esc_html_e( 'Subscribe →', 'vmra' ); ?></button>
		</form>
	</div>
</section>

<?php
get_footer();
