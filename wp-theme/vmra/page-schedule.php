<?php
/**
 * Template for the /schedule/ page.
 * Ported from the static public/schedule/index.html.
 *
 * WP auto-loads this template when a Page with slug "schedule" is viewed.
 * Per-page CSS stays inline to match the static site 1:1.
 * Data-driven JS fetches point at /wp-content/themes/vmra/data/ via str_replace.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vmra_data_base = esc_url( VMRA_THEME_URI . '/data' );

// Round counters come from the data so the header can't drift from the list.
$vmra_sched_standings = function_exists( 'vmra_seed_data' ) ? vmra_seed_data( 'standings' ) : null;
$vmra_sched_schedule  = function_exists( 'vmra_seed_data' ) ? vmra_seed_data( 'schedule' ) : null;
$vmra_sched_done      = (int) ( $vmra_sched_standings['rounds_completed'] ?? 0 );
$vmra_sched_total     = is_array( $vmra_sched_schedule ) ? count( $vmra_sched_schedule['races'] ?? array() ) : 0;
$vmra_sched_updated   = (string) ( $vmra_sched_standings['updated'] ?? '' );

$vmra_sched_line = 'Schedule';
if ( $vmra_sched_updated ) {
	$vmra_sched_line = sprintf(
		'Updated %s · Round %02d complete',
		gmdate( 'M j, Y', strtotime( $vmra_sched_updated ) ),
		$vmra_sched_done
	);
}

$vmra_sched_left = max( 0, $vmra_sched_total - $vmra_sched_done );
$vmra_sched_h1   = $vmra_sched_left > 0
	? sprintf( 'Round %02d Done.<br>%d More to Run.', $vmra_sched_done, $vmra_sched_left )
	: 'The 40th Season.';

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

.race-list{display:grid;gap:24px}
.race{background:var(--asphalt-2);border:1px solid var(--grease);padding:0;display:flex;flex-direction:column;transition:border-color .2s;overflow:hidden}
.race:hover{border-color:var(--race-red)}
.race-head{display:grid;grid-template-columns:120px 1fr auto;gap:24px;align-items:center;padding:24px 28px}
.race-num{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-size:.7rem;letter-spacing:.2em;text-transform:uppercase}
.race-date{font-family:'Anton',sans-serif;font-size:1.1rem;line-height:1.1;margin-top:6px}
.race-meta{display:flex;flex-direction:column;gap:6px}
.race-track{font-family:'Anton',sans-serif;font-size:1.5rem;line-height:1.1}
.race-event{color:var(--chalk-dim);font-size:.92rem;font-style:italic}
.race-loc{color:var(--chalk-dim);font-size:.85rem;font-family:'JetBrains Mono',monospace;letter-spacing:.04em}
.race-tag{font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.15em;color:var(--race-red);text-transform:uppercase;text-align:right;padding:8px 14px;border:1px solid var(--race-red);border-radius:2px;font-weight:700;white-space:nowrap}
.race-tag.opener{color:var(--sodium);border-color:var(--sodium)}
.race-tag.feature{color:var(--chalk);border-color:var(--sodium);background:var(--sodium);color:var(--asphalt)}
.race-tag.finale{color:var(--chalk);border-color:var(--chalk);background:var(--race-red)}
.race-tag.completed{color:var(--chalk-dim);border-color:var(--grease);background:transparent;font-weight:500}
.race-tag.completed::before{content:"✓ ";color:var(--sodium);font-weight:700}
.race-tag.tbd{color:var(--chalk-dim);border-color:var(--grease);background:transparent;border-style:dashed;font-weight:500}
.race.completed{opacity:.72}
.race.completed:hover{opacity:1}
.race.tbd{background:transparent;border-style:dashed;border-color:var(--grease)}

.race-track-block{display:grid;grid-template-columns:1.2fr 1fr;border-top:1px solid var(--grease);background:#050507}
.race-map{position:relative;background:var(--asphalt-3);min-height:240px;overflow:hidden}
.race-map iframe{display:block;width:100%;height:100%;border:0;filter:saturate(.85) contrast(1.05);transition:filter .25s}
.race:hover .race-map iframe{filter:saturate(1) contrast(1.05)}
.race-actions{padding:24px 28px;display:flex;flex-direction:column;justify-content:center;gap:14px}
.race-actions .label{font-family:'JetBrains Mono',monospace;font-size:.68rem;letter-spacing:.18em;color:var(--sodium);text-transform:uppercase}
.race-actions .address{font-family:'Space Grotesk',sans-serif;font-size:1rem;color:var(--chalk);line-height:1.45}
.race-actions .shape{font-family:'JetBrains Mono',monospace;font-size:.78rem;color:var(--chalk-dim);letter-spacing:.04em}
.race-btn{display:inline-flex;align-items:center;justify-content:space-between;gap:10px;padding:12px 18px;font-family:'JetBrains Mono',monospace;font-size:.78rem;letter-spacing:.12em;text-transform:uppercase;text-decoration:none;border:1px solid var(--grease);color:var(--chalk);background:var(--asphalt-2);transition:all .15s;font-weight:600}
.race-btn:hover{border-color:var(--race-red);color:var(--sodium);background:var(--asphalt)}
.race-btn.primary{background:var(--race-red);border-color:var(--race-red);color:var(--chalk)}
.race-btn.primary:hover{background:var(--asphalt);color:var(--sodium);border-color:var(--sodium)}
.race-btn .arrow{font-family:'Anton',sans-serif;font-size:1rem}
.race-btns{display:flex;flex-direction:column;gap:8px;margin-top:4px}

@media (max-width:820px){
  .race-track-block{grid-template-columns:1fr}
  .race-map{min-height:220px}
}
@media (max-width:620px){
  .race-head{grid-template-columns:1fr;gap:10px;padding:18px 20px}
  .race-tag{justify-self:start}
  .race-actions{padding:18px 20px}
}

/* Weather forecast bar */
.race-weather{display:flex;align-items:center;gap:16px;padding:12px 28px;border-top:1px solid var(--grease);background:linear-gradient(90deg,rgba(42,93,143,.12),transparent 60%);font-family:'JetBrains Mono',monospace;font-size:.75rem;letter-spacing:.08em;color:var(--chalk-dim);flex-wrap:wrap}
.race-weather .wx-icon{font-size:1.4rem;line-height:1}
.race-weather .wx-temp{font-family:'Anton',sans-serif;font-size:1.3rem;color:var(--sodium);letter-spacing:0}
.race-weather .wx-cond{text-transform:uppercase;letter-spacing:.14em;color:var(--chalk)}
.race-weather .wx-detail{display:flex;gap:14px;margin-left:auto}
.race-weather .wx-detail span{white-space:nowrap}
.race-weather .wx-rain{color:#5ba8f7}
.race-weather .wx-wind{color:var(--chalk-dim)}
.race-weather .wx-label{font-size:.65rem;letter-spacing:.18em;text-transform:uppercase;color:var(--chalk-dim);opacity:.7}
.race-weather.wx-pending{justify-content:center;opacity:.5}
.race.completed .race-weather{display:none}
@media(max-width:620px){
  .race-weather{padding:10px 20px;gap:10px;font-size:.68rem}
  .race-weather .wx-detail{margin-left:0;width:100%}
}

.note{background:var(--asphalt-2);border-left:3px solid var(--race-red);padding:18px 24px;margin:30px 0;font-size:.92rem;color:var(--chalk-dim)}
</style>

<?php
$body = <<<'VMRA_BODY_EOT'
<section class="hero"><div class="hero-inner">
  <span class="eyebrow">§ 2026 · 40th Anniversary Tour</span>
  <h1>VMRA_SCHED_H1</h1>
  <p class="lede">Steve Woods #22 finally got the night he'd been chasing at the CARS Tour Mark Galloway Shootout — quick time, then the full 25 in the main, 63 points on the board and the best haul anybody's put together this year. All it bought him was one spot. That's how tight this thing is. Up front, Bart Hecter Jr #68 and Jason Quatsoe #8 traded blows all evening and finished separated by a single point on the night, which leaves ten between them in the book with five points races to go. Kahl Cheth #23 sits third, Vince Conwell #82 fourth in the Northwest Concrete Cutting car. Next up: the Ron Rohde Memorial at Stateline on July 11 — no points, all heart — then the 40th Anniversary Bash at South Sound to open the back half. Eleven dates on the card. Nine that pay. Two we run because that's what this club is.</p>
</div></section>

<main id="main-content" tabindex="-1">
  <p class="schedule-updated" id="scheduleUpdated" style="font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.12em;color:var(--chalk-dim);text-transform:uppercase;margin:0 0 20px;text-align:right">VMRA_SCHED_UPDATED</p>

  <div class="race-list" id="raceList">
    <!-- Pre-rendered at authoring time from /data/schedule.json. The JS below re-hydrates this container from the live JSON on every page load. -->
    <div class="race completed">
      <div class="race-head">
        <div><div class="race-num">Race 01</div><div class="race-date">Apr 12</div></div>
        <div class="race-meta">
          <div class="race-track">Tri-City Raceway</div>
          <div class="race-event">57th Apple Cup VMRA Season Opener</div>
          <div class="race-loc">West Richland, WA</div>
        </div>
        <div class="race-tag completed">Round 1 · Complete</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=8280%20W%20Van%20Giesen%20St%2C%20West%20Richland%2C%20WA%2099353&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Tri-City Raceway satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">8280 W Van Giesen St, West Richland, WA 99353</div>
          <div class="shape">3/8-mile paved oval</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=8280%20W%20Van%20Giesen%20St%2C%20West%20Richland%2C%20WA%2099353" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://www.tricityraceway.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 02</div><div class="race-date">Apr 25</div></div>
        <div class="race-meta">
          <div class="race-track">Evergreen Speedway</div>
          <div class="race-event">Grocery Outlet Night</div>
          <div class="race-loc">Monroe, WA</div>
        </div>
        <div class="race-tag ">This Weekend</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Evergreen Speedway satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">14405 179th Ave SE, Monroe, WA 98272</div>
          <div class="shape">5/8-mile paved oval — &quot;The Half-Mile&quot;</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://evergreenspeedway.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 03</div><div class="race-date">May 2</div></div>
        <div class="race-meta">
          <div class="race-track">Wenatchee Valley Super Oval</div>
          <div class="race-event">Apple Blossom Rubber Down</div>
          <div class="race-loc">East Wenatchee, WA</div>
        </div>
        <div class="race-tag ">Round 3</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=2850%20Gun%20Club%20Rd%2C%20East%20Wenatchee%2C%20WA%2098802&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Wenatchee Valley Super Oval satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">2850 Gun Club Rd, East Wenatchee, WA 98802</div>
          <div class="shape">1/4-mile paved oval</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=2850%20Gun%20Club%20Rd%2C%20East%20Wenatchee%2C%20WA%2098802" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://www.wvso.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 04</div><div class="race-date">Jun 27</div></div>
        <div class="race-meta">
          <div class="race-track">Evergreen Speedway</div>
          <div class="race-event">CARS Tour Mark Galloway Shootout</div>
          <div class="race-loc">Monroe, WA</div>
        </div>
        <div class="race-tag ">Round 4</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Evergreen Speedway satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">14405 179th Ave SE, Monroe, WA 98272</div>
          <div class="shape">5/8-mile paved oval — &quot;The Half-Mile&quot;</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://evergreenspeedway.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 05</div><div class="race-date">Jul 11</div></div>
        <div class="race-meta">
          <div class="race-track">Stateline Speedway</div>
          <div class="race-event">Ron Rohde Memorial</div>
          <div class="race-loc">Post Falls, ID</div>
        </div>
        <div class="race-tag ">Special · Non-Points</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=1349%20N%20Beck%20Rd%2C%20Post%20Falls%2C%20ID%2083854&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Stateline Speedway satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">1349 N Beck Rd, Post Falls, ID 83854</div>
          <div class="shape">1/4-mile banked paved oval</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=1349%20N%20Beck%20Rd%2C%20Post%20Falls%2C%20ID%2083854" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://www.raceidaho.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 06</div><div class="race-date">Jul 25</div></div>
        <div class="race-meta">
          <div class="race-track">South Sound Speedway</div>
          <div class="race-event">VMRA 40th Anniversary Bash</div>
          <div class="race-loc">Rochester, WA</div>
        </div>
        <div class="race-tag feature">40th Anniversary Bash</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=3730%20183rd%20Ave%20SW%2C%20Rochester%2C%20WA%2098579&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="South Sound Speedway satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">3730 183rd Ave SW, Rochester, WA 98579</div>
          <div class="shape">3/8-mile paved oval + figure-8</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=3730%20183rd%20Ave%20SW%2C%20Rochester%2C%20WA%2098579" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://www.southsoundspeedway.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 07</div><div class="race-date">Aug 15</div></div>
        <div class="race-meta">
          <div class="race-track">Evergreen Speedway</div>
          <div class="race-event">Tire Pros Summer Showdown</div>
          <div class="race-loc">Monroe, WA</div>
        </div>
        <div class="race-tag ">Round 7</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Evergreen Speedway satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">14405 179th Ave SE, Monroe, WA 98272</div>
          <div class="shape">5/8-mile paved oval — &quot;The Half-Mile&quot;</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://evergreenspeedway.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 08</div><div class="race-date">Aug 22</div></div>
        <div class="race-meta">
          <div class="race-track">Wenatchee Valley Super Oval</div>
          <div class="race-event">Thunder in the Valley Open Wheel Show</div>
          <div class="race-loc">East Wenatchee, WA</div>
        </div>
        <div class="race-tag ">Round 8</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=2850%20Gun%20Club%20Rd%2C%20East%20Wenatchee%2C%20WA%2098802&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Wenatchee Valley Super Oval satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">2850 Gun Club Rd, East Wenatchee, WA 98802</div>
          <div class="shape">1/4-mile paved oval</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=2850%20Gun%20Club%20Rd%2C%20East%20Wenatchee%2C%20WA%2098802" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://www.wvso.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 09</div><div class="race-date">Sep 19</div></div>
        <div class="race-meta">
          <div class="race-track">Evergreen Speedway</div>
          <div class="race-event">NASCAR Championship Night</div>
          <div class="race-loc">Monroe, WA</div>
        </div>
        <div class="race-tag ">Round 9</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Evergreen Speedway satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">14405 179th Ave SE, Monroe, WA 98272</div>
          <div class="shape">5/8-mile paved oval — &quot;The Half-Mile&quot;</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=14405%20179th%20Ave%20SE%2C%20Monroe%2C%20WA%2098272" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://evergreenspeedway.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 10</div><div class="race-date">Oct 3</div></div>
        <div class="race-meta">
          <div class="race-track">Tri-City Raceway</div>
          <div class="race-event">Fall Classic VMRA Season Championship Night</div>
          <div class="race-loc">West Richland, WA</div>
        </div>
        <div class="race-tag finale">Season Championship Night</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=8280%20W%20Van%20Giesen%20St%2C%20West%20Richland%2C%20WA%2099353&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Tri-City Raceway satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">8280 W Van Giesen St, West Richland, WA 99353</div>
          <div class="shape">3/8-mile paved oval</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=8280%20W%20Van%20Giesen%20St%2C%20West%20Richland%2C%20WA%2099353" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://www.tricityraceway.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
    <div class="race">
      <div class="race-head">
        <div><div class="race-num">Race 11</div><div class="race-date">Oct 4</div></div>
        <div class="race-meta">
          <div class="race-track">Tri-City Raceway</div>
          <div class="race-event">Fall Classic Vintage Modified Open Comp</div>
          <div class="race-loc">West Richland, WA</div>
        </div>
        <div class="race-tag ">Special · Non-Points</div>
      </div>
      <div class="race-track-block">
        <div class="race-map">
          <iframe src="https://maps.google.com/maps?q=8280%20W%20Van%20Giesen%20St%2C%20West%20Richland%2C%20WA%2099353&amp;t=k&amp;z=17&amp;ie=UTF8&amp;output=embed" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="Tri-City Raceway satellite map"></iframe>
        </div>
        <div class="race-actions">
          <div class="label">Track Address</div>
          <div class="address">8280 W Van Giesen St, West Richland, WA 99353</div>
          <div class="shape">3/8-mile paved oval</div>
          <div class="race-btns">
            <a class="race-btn primary" href="https://www.google.com/maps/dir/?api=1&amp;destination=8280%20W%20Van%20Giesen%20St%2C%20West%20Richland%2C%20WA%2099353" target="_blank" rel="noopener">Get Directions <span class="arrow">→</span></a>
            <a class="race-btn" href="https://www.tricityraceway.com/" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script>
  (function(){
    var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
    function escHtml(s){ return String(s).replace(/[&<>"']/g, function(c){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]; }); }
    function escAttr(s){ return escHtml(s); }
    fetch('/data/schedule.json')
      .then(function(r){ return r.json(); })
      .then(function(data){
        var tracks = data.tracks || {};
        var html = data.races.map(function(race){
          var d = new Date(race.date + 'T12:00:00-07:00');
          var dateLabel = months[d.getMonth()] + ' ' + d.getDate();
          var roundNum = race.round === 'TBD' ? 'TBD' : String(race.round).padStart(2, '0');
          var validTags = ['opener','finale','feature','completed','tbd'];
          var tagClass = validTags.indexOf(race.tag) !== -1 ? race.tag : '';
          var rowClass = (race.tag === 'completed' || race.tag === 'tbd') ? race.tag : '';
          var dateDisplay = race.tag === 'tbd' ? '—' : dateLabel;
          var info = tracks[race.track] || {};
          var addr = info.address || '';
          var encAddr = encodeURIComponent(addr || race.track);
          var mapSrc = 'https://maps.google.com/maps?q=' + encAddr + '&t=k&z=17&ie=UTF8&output=embed';
          var dirHref = 'https://www.google.com/maps/dir/?api=1&destination=' + encAddr;
          var siteHref = info.website || '';

          var head = '<div class="race-head">' +
              '<div><div class="race-num">Race ' + roundNum + '</div><div class="race-date">' + dateDisplay + '</div></div>' +
              '<div class="race-meta">' +
                '<div class="race-track">' + escHtml(race.track) + '</div>' +
                (race.event_name ? '<div class="race-event">' + escHtml(race.event_name) + '</div>' : '') +
                '<div class="race-loc">' + escHtml(info.city || race.location) + '</div>' +
              '</div>' +
              '<div class="race-tag ' + tagClass + '">' + escHtml(race.tag_label) + '</div>' +
            '</div>';

          // Weather placeholder for upcoming races
          var weatherBar = '';
          if (race.tag !== 'completed') {
            weatherBar = '<div class="race-weather wx-pending" id="wx-r' + race.round + '" data-lat="' + (info.lat || '') + '" data-lon="' + (info.lon || '') + '" data-date="' + race.date + '" data-track="' + escAttr(race.track) + '"><span class="wx-label">Loading forecast…</span></div>';
          }

          var trackBlock = '';
          if (addr) {
            trackBlock = '<div class="race-track-block">' +
                '<div class="race-map">' +
                  '<iframe src="' + escAttr(mapSrc) + '" loading="lazy" referrerpolicy="no-referrer-when-downgrade" allowfullscreen title="' + escAttr(race.track + ' satellite map') + '"></iframe>' +
                '</div>' +
                '<div class="race-actions">' +
                  '<div class="label">Track Address</div>' +
                  '<div class="address">' + escHtml(addr) + '</div>' +
                  (info.shape ? '<div class="shape">' + escHtml(info.shape) + '</div>' : '') +
                  '<div class="race-btns">' +
                    (race.round !== 'TBD' ? '<a class="race-btn primary" href="/races/round-' + roundNum + '/">View Round ' + roundNum + ' Details <span class="arrow">→</span></a>' : '') +
                    '<a class="race-btn" href="' + escAttr(dirHref) + '" target="_blank" rel="noopener">Get Directions <span class="arrow">↗</span></a>' +
                    (siteHref ? '<a class="race-btn" href="' + escAttr(siteHref) + '" target="_blank" rel="noopener">Track Website <span class="arrow">↗</span></a>' : '') +
                  '</div>' +
                '</div>' +
              '</div>';
          }

          return '<div class="race ' + rowClass + '">' + head + weatherBar + trackBlock + '</div>';
        }).join('');
        document.getElementById('raceList').innerHTML = html;

        // Fetch weather for each upcoming race
        fetchAllWeather();
      })
      .catch(function(){
        document.getElementById('raceList').innerHTML =
          '<div style="text-align:center;color:var(--chalk-dim);padding:40px">Schedule temporarily unavailable. Try refreshing.</div>';
      });
  })();

  /* ===== Weather forecast for each race (Open-Meteo, no API key) ===== */
  function wxIcon(code) {
    if (code === 0) return '☀️';
    if (code <= 3)  return '⛅';
    if (code <= 48) return '🌫️';
    if (code <= 57) return '🌦️';
    if (code <= 67) return '🌧️';
    if (code <= 77) return '❄️';
    if (code <= 82) return '🌧️';
    if (code <= 86) return '🌨️';
    if (code <= 99) return '⛈️';
    return '🌤️';
  }
  function wxLabel(code) {
    if (code === 0) return 'Clear skies';
    if (code <= 3)  return 'Partly cloudy';
    if (code <= 48) return 'Foggy';
    if (code <= 55) return 'Drizzle';
    if (code <= 57) return 'Freezing drizzle';
    if (code <= 65) return 'Rain';
    if (code <= 67) return 'Freezing rain';
    if (code <= 77) return 'Snow';
    if (code <= 82) return 'Rain showers';
    if (code <= 86) return 'Snow showers';
    if (code <= 99) return 'Thunderstorms';
    return '';
  }
  function fetchAllWeather() {
    var bars = document.querySelectorAll('.race-weather[data-lat]');
    var today = new Date(); today.setHours(0,0,0,0);
    bars.forEach(function(bar) {
      var lat = parseFloat(bar.dataset.lat);
      var lon = parseFloat(bar.dataset.lon);
      var raceDate = bar.dataset.date;
      var track = bar.dataset.track;
      if (!lat || !lon || !raceDate) {
        bar.innerHTML = '<span class="wx-label">Forecast unavailable — coordinates not set</span>';
        return;
      }
      var rd = new Date(raceDate + 'T12:00:00-07:00'); rd.setHours(0,0,0,0);
      var daysOut = Math.round((rd - today) / 86400000);
      if (daysOut > 16) {
        bar.classList.remove('wx-pending');
        bar.innerHTML = '<span class="wx-icon">📅</span><span class="wx-label">Race-day forecast available ' + (daysOut - 14) + ' days before race day</span>';
        return;
      }
      if (daysOut < 0) {
        bar.style.display = 'none';
        return;
      }
      var url = 'https://api.open-meteo.com/v1/forecast'
        + '?latitude=' + lat
        + '&longitude=' + lon
        + '&daily=temperature_2m_max,temperature_2m_min,precipitation_probability_max,wind_speed_10m_max,weather_code'
        + '&temperature_unit=fahrenheit'
        + '&wind_speed_unit=mph'
        + '&timezone=America/Los_Angeles'
        + '&start_date=' + raceDate
        + '&end_date=' + raceDate;
      fetch(url)
        .then(function(r){ if (!r.ok) throw new Error('HTTP ' + r.status); return r.json(); })
        .then(function(wx){
          var d = wx && wx.daily;
          if (!d || !d.temperature_2m_max || !d.temperature_2m_max.length) {
            bar.innerHTML = '<span class="wx-label">Forecast data unavailable</span>';
            bar.classList.remove('wx-pending');
            return;
          }
          var hi   = Math.round(d.temperature_2m_max[0]);
          var lo   = Math.round(d.temperature_2m_min[0]);
          var pop  = Math.round((d.precipitation_probability_max && d.precipitation_probability_max[0]) || 0);
          var wind = Math.round((d.wind_speed_10m_max && d.wind_speed_10m_max[0]) || 0);
          var code = (d.weather_code && d.weather_code[0]) || 0;
          var icon = wxIcon(code);
          var label = wxLabel(code);
          var prefix = daysOut === 0 ? 'Race-Day Weather'
                     : daysOut === 1 ? 'Tomorrow\'s Forecast'
                     : daysOut <= 3  ? 'Race Weekend Forecast'
                     : 'Race-Day Forecast';
          var html = '<span class="wx-icon">' + icon + '</span>'
            + '<span class="wx-temp">' + hi + '°</span>'
            + '<span class="wx-cond">' + label + '</span>'
            + '<span class="wx-detail">'
            + '<span>Hi ' + hi + '° / Lo ' + lo + '°</span>';
          if (pop >= 10) html += '<span class="wx-rain">💧 ' + pop + '% rain</span>';
          if (wind >= 8) html += '<span class="wx-wind">💨 ' + wind + ' mph</span>';
          html += '</span>'
            + '<span class="wx-label">' + prefix + ' · ' + track.replace(' Speedway','').replace(' Raceway','').replace(' Super Oval','') + '</span>';
          bar.innerHTML = html;
          bar.classList.remove('wx-pending');
        })
        .catch(function(){
          bar.innerHTML = '<span class="wx-label">Forecast temporarily unavailable</span>';
          bar.classList.remove('wx-pending');
        });
    });
  }
  </script>

  <div class="note"><strong>Pre-registration:</strong> The Apple Cup uses Tri-City Raceway's online sign-up — head to <a href="https://tricityraceway.com/drivers.html" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor" target="_blank" rel="noopener">tricityraceway.com/drivers.html</a> for the form. For all other rounds, contact the board at <a href="mailto:vmrainfo@gmail.com" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor">vmrainfo@gmail.com</a>.</div>
</main>
VMRA_BODY_EOT;

// Retarget /data/*.json fetches at the theme's data dir.
$body = str_replace( "'/data/", "'" . $vmra_data_base . "/", $body );
$body = str_replace( '"/data/', '"' . $vmra_data_base . '/', $body );
$body = str_replace( 'VMRA_SCHED_UPDATED', esc_html( $vmra_sched_line ), $body );
$body = str_replace( 'VMRA_SCHED_H1', $vmra_sched_h1, $body );
echo $body;
?>

<?php get_footer();
