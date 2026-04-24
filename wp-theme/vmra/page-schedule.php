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

.note{background:var(--asphalt-2);border-left:3px solid var(--race-red);padding:18px 24px;margin:30px 0;font-size:.92rem;color:var(--chalk-dim)}
</style>

<?php
$body = <<<'VMRA_BODY_EOT'
<section class="hero"><div class="hero-inner">
  <span class="eyebrow">§ 2026 · 40th Anniversary Tour</span>
  <h1>Apple Cup Done.<br>Ten More to Run.</h1>
  <p class="lede">Kahl Cheth #23 took the 57th running of the Apple Cup at Tri-City — main event winner by the full 25-point margin on a night the defending champ didn't unload. Up next: Grocery Outlet Night at Evergreen this Saturday, then Apple Blossom Rubber Down at Wenatchee the following weekend, the CARS Tour Mark Galloway Shootout in June, the Ron Rohde Memorial at Stateline (non-points) in July, the 40th Anniversary Bash at South Sound late July, and a four-race summer/fall sprint into the Fall Classic at Tri-City October 3-4. Eleven dates total. Nine for points. Two for the love of it.</p>
</div></section>

<main id="main-content" tabindex="-1">
  <p class="schedule-updated" id="scheduleUpdated" style="font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.12em;color:var(--chalk-dim);text-transform:uppercase;margin:0 0 20px;text-align:right">Updated Apr 23, 2026 · Round 01 complete</p>

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

          return '<div class="race ' + rowClass + '">' + head + trackBlock + '</div>';
        }).join('');
        document.getElementById('raceList').innerHTML = html;
      })
      .catch(function(){
        document.getElementById('raceList').innerHTML =
          '<div style="text-align:center;color:var(--chalk-dim);padding:40px">Schedule temporarily unavailable. Try refreshing.</div>';
      });
  })();
  </script>

  <div class="note"><strong>Pre-registration:</strong> The Apple Cup uses Tri-City Raceway's online sign-up — head to <a href="https://tricityraceway.com/drivers.html" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor" target="_blank" rel="noopener">tricityraceway.com/drivers.html</a> for the form. For all other rounds, contact the board at <a href="mailto:vmrainfo@gmail.com" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor">vmrainfo@gmail.com</a>.</div>
</main>
VMRA_BODY_EOT;

// Retarget /data/*.json fetches at the theme's data dir.
$body = str_replace( "'/data/", "'" . $vmra_data_base . "/", $body );
$body = str_replace( '"/data/', '"' . $vmra_data_base . '/', $body );
echo $body;
?>

<?php get_footer();
