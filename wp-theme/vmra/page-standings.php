<?php
/**
 * Template for the /standings/ page.
 * Ported from the static public/standings/index.html.
 *
 * WP auto-loads this template when a Page with slug "standings" is viewed.
 * Per-page CSS stays inline to match the static site 1:1.
 * Data-driven JS fetches point at /wp-content/themes/vmra/data/ via str_replace.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vmra_data_base = esc_url( VMRA_THEME_URI . '/data' );

// Pre-render the standings rows + build a car→URL map for the JS hydrator
// so both the no-JS fallback and the JS render link driver names to bios.
$vmra_standings = function_exists( 'vmra_seed_data' ) ? vmra_seed_data( 'standings' ) : null;
$vmra_driver_url_map = array();
$vmra_standings_rows_html = '';
if ( is_array( $vmra_standings ) && ! empty( $vmra_standings['drivers'] ) ) {
	foreach ( $vmra_standings['drivers'] as $row ) {
		$car  = (string) ( $row['car']  ?? '' );
		$name = (string) ( $row['name'] ?? '' );
		$pos  = (int)    ( $row['position'] ?? 0 );
		$pts  = (int)    ( $row['points']   ?? 0 );
		$url  = $car ? vmra_driver_url_by_car( $car ) : '';
		if ( $url ) {
			$vmra_driver_url_map[ $car ] = $url;
		}
		$name_cell = $url
			? '<a href="' . esc_url( $url ) . '" style="color:inherit;text-decoration:none;border-bottom:1px solid var(--grease)">' . esc_html( $name ) . '</a>'
			: esc_html( $name );
		$vmra_standings_rows_html .= '<tr>'
			. '<td class="pos">' . $pos . '</td>'
			. '<td class="car">#' . esc_html( $car ) . '</td>'
			. '<td class="name">' . $name_cell . '</td>'
			. '<td class="pts">' . $pts . '</td>'
			. '</tr>';
	}
}

get_header(); ?>

<style>
:root{
  --asphalt:#0e0e10;--asphalt-2:#17171a;--asphalt-3:#212126;--grease:#2a2a30;
  --chalk:#f4ede1;--chalk-dim:#c9c0ae;--race-red:#d11a2a;--sodium:#ffb319;--engine-blue:#2a5d8f;
}
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:'Space Grotesk',-apple-system,sans-serif;background:var(--asphalt);color:var(--chalk);line-height:1.6;-webkit-font-smoothing:antialiased}
a{color:inherit}

.hero{padding:60px 5vw 40px;border-bottom:1px solid var(--grease);background:linear-gradient(180deg,var(--asphalt-2),var(--asphalt))}
.hero-inner{max-width:1080px;margin:0 auto}
.eyebrow{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-size:.78rem;letter-spacing:.2em;text-transform:uppercase;margin-bottom:14px}
h1{font-family:'Anton',sans-serif;font-size:clamp(2.5rem,6vw,4.5rem);letter-spacing:.02em;line-height:1;margin-bottom:18px}
.lede{font-size:1.15rem;color:var(--chalk-dim);max-width:740px}

main{max-width:1080px;margin:0 auto;padding:60px 5vw}

.standings{width:100%;border-collapse:collapse;font-family:'Space Grotesk',sans-serif}
.standings thead{background:var(--asphalt-2);border-top:2px solid var(--race-red)}
.standings th{padding:14px 18px;text-align:left;font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.18em;text-transform:uppercase;color:var(--sodium);border-bottom:1px solid var(--grease)}
.standings td{padding:16px 18px;border-bottom:1px solid var(--grease);vertical-align:middle}
.standings tbody tr:hover{background:var(--asphalt-2)}
.standings .pos{font-family:'Anton',sans-serif;font-size:1.4rem;color:var(--sodium);width:60px}
.standings .car{font-family:'JetBrains Mono',monospace;color:var(--chalk-dim);font-size:.95rem;width:80px}
.standings .name{font-weight:600;font-size:1.05rem}
.standings .pts{font-family:'JetBrains Mono',monospace;font-weight:700;text-align:right;color:var(--chalk);font-size:1.05rem}
.standings tr:nth-child(1) .pos{color:var(--race-red)}
.standings tr:nth-child(2) .pos,.standings tr:nth-child(3) .pos{color:var(--sodium)}
@media (max-width:620px){
  .standings th,.standings td{padding:10px 8px;font-size:.85rem}
  .standings .pos{font-size:1.1rem;width:36px}
  .standings .car{width:50px;font-size:.78rem}
}

.note{background:var(--asphalt-2);border-left:3px solid var(--race-red);padding:18px 24px;margin:30px 0;font-size:.92rem;color:var(--chalk-dim)}
</style>

<?php
$body = <<<'VMRA_BODY_EOT'
<section class="hero"><div class="hero-inner">
  <span class="eyebrow">§ 2026 YTD · After Round 01 of 11</span>
  <h1>Cheth Leads the 40th.</h1>
  <p class="lede">One round down. Kahl Cheth #23 leads the 2026 points at 64 after taking the main at the 57th Apple Cup at Tri-City. Jason Quatsoe #8 sits four back at 60. Steve Woods #22 is another three behind at 57. Defending champ Kyten Jones #30 didn't unload on the night — the title race opens wide. Ten rounds left. Points stay with the car, not the driver.</p>
</div></section>

<main id="main-content" tabindex="-1">
  <table class="standings">
    <thead>
      <tr>
        <th>Pos</th>
        <th>Car</th>
        <th>Driver</th>
        <th class="pts">Points</th>
      </tr>
    </thead>
    <tbody id="standingsBody">VMRA_STANDINGS_ROWS</tbody>
  </table>
  <p id="standingsUpdated" style="font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.12em;color:var(--chalk-dim);text-transform:uppercase;margin-top:14px;text-align:right">Updated Apr 23, 2026 · 1 round completed</p>

  <script>
  (function(){
    var urlMap = window.vmraDriverUrls || {};
    function escapeHtml(s){ return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    fetch('/data/standings.json')
      .then(function(r){ return r.json(); })
      .then(function(data){
        var rows = data.drivers.map(function(d){
          var name = escapeHtml(d.name);
          var nameCell = urlMap[d.car]
            ? '<a href="' + urlMap[d.car] + '" style="color:inherit;text-decoration:none;border-bottom:1px solid var(--grease)">' + name + '</a>'
            : name;
          return '<tr>' +
            '<td class="pos">' + d.position + '</td>' +
            '<td class="car">#' + escapeHtml(d.car) + '</td>' +
            '<td class="name">' + nameCell + '</td>' +
            '<td class="pts">' + d.points + '</td>' +
            '</tr>';
        }).join('');
        document.getElementById('standingsBody').innerHTML = rows;
        if (data.updated) {
          var dt = new Date(data.updated + 'T12:00:00');
          var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
          document.getElementById('standingsUpdated').textContent =
            'Updated ' + months[dt.getMonth()] + ' ' + dt.getDate() + ', ' + dt.getFullYear() +
            (data.rounds_completed ? ' · ' + data.rounds_completed + ' rounds completed' : '');
        }
      })
      .catch(function(){
        document.getElementById('standingsBody').innerHTML =
          '<tr><td colspan="4" style="text-align:center;color:var(--chalk-dim);padding:30px">Standings temporarily unavailable. Try refreshing.</td></tr>';
      });
  })();
  </script>

  <div class="note"><strong>Scoring reminder:</strong> Time-ins pay 20 down to 1, A-heats 15 down to 1, B-heats 13 down to 1. Main events pay 25 for the win, then −3, −2, −1 down the order. Points stay with the car number; rookies get the same scale minus the show-up bonus. Full breakdown on the <a href="/rules/" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor">Rules page</a>.</div>
</main>
VMRA_BODY_EOT;

// Retarget /data/*.json fetches at the theme's data dir.
$body = str_replace( "'/data/", "'" . $vmra_data_base . "/", $body );
$body = str_replace( '"/data/', '"' . $vmra_data_base . '/', $body );
// Substitute the pre-rendered standings rows (with driver-bio links baked in).
$body = str_replace( 'VMRA_STANDINGS_ROWS', $vmra_standings_rows_html, $body );
// Publish the car→URL map so the JS hydrator links names the same way.
echo '<script id="vmra-driver-urls">window.vmraDriverUrls = '
	. wp_json_encode( $vmra_driver_url_map, JSON_UNESCAPED_SLASHES )
	. ';</script>';
echo $body;
?>

<?php get_footer();
