<?php
/**
 * Template for the /racers/ page.
 * Ported from the static public/racers/index.html.
 *
 * WP auto-loads this template when a Page with slug "racers" is viewed.
 * Per-page CSS stays inline to match the static site 1:1.
 * Data-driven JS fetches point at /wp-content/themes/vmra/data/ via str_replace.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$vmra_data_base = esc_url( VMRA_THEME_URI . '/data' );

get_header(); ?>

<style>
:root {
  --asphalt: #0e0e10;
  --asphalt-2: #17171a;
  --asphalt-3: #212126;
  --grease: #2a2a30;
  --chalk: #f4ede1;
  --chalk-dim: #c9c0ae;
  --race-red: #d11a2a;
  --race-red-hot: #ff2a3c;
  --sodium: #ffb319;
  --sodium-hot: #ffd060;
  --engine-blue: #2a5d8f;
  --rust: #9a3b1e;
}

* { box-sizing: border-box; margin: 0; padding: 0; }
html { scroll-behavior: smooth; }
body {
  font-family: 'Space Grotesk', -apple-system, BlinkMacSystemFont, sans-serif;
  background: var(--asphalt);
  color: var(--chalk);
  line-height: 1.55;
  -webkit-font-smoothing: antialiased;
}
a { color: inherit; }

.cb-subscribe { color: var(--sodium); font-weight: 700; }

/* ===== HERO ===== */
.roster-hero {
  padding: 64px 5vw 48px;
  max-width: 1280px; margin: 0 auto;
  position: relative;
  border-bottom: 1px dashed var(--grease);
}
.roster-hero::before {
  content: ""; position: absolute; inset: 0; pointer-events: none;
  background-image:
    linear-gradient(rgba(244,237,225,0.02) 1px, transparent 1px),
    linear-gradient(90deg, rgba(244,237,225,0.02) 1px, transparent 1px);
  background-size: 80px 80px;
  mask-image: radial-gradient(ellipse at center, black 30%, transparent 75%);
}
.roster-hero .marker {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.72rem; letter-spacing: 0.24em; text-transform: uppercase;
  color: var(--race-red); font-weight: 700; margin-bottom: 14px;
  display: inline-flex; align-items: center; gap: 10px;
}
.roster-hero .marker::before {
  content: ""; width: 8px; height: 8px; background: var(--race-red);
  box-shadow: 0 0 6px var(--race-red);
}
.roster-hero h1 {
  font-family: 'Anton', sans-serif;
  font-size: clamp(2.4rem, 6vw, 4.4rem);
  text-transform: uppercase; letter-spacing: 0.01em; line-height: 0.95;
  margin-bottom: 18px;
  position: relative;
}
.roster-hero h1 .accent { color: var(--sodium); }
.roster-hero .lede {
  max-width: 760px;
  font-size: 1.02rem; color: var(--chalk-dim); line-height: 1.6;
}
.roster-hero .lede strong { color: var(--chalk); font-weight: 600; }

.roster-stats {
  display: flex; gap: 28px; margin-top: 24px; flex-wrap: wrap;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.68rem; letter-spacing: 0.18em; text-transform: uppercase;
  color: var(--chalk-dim);
}
.roster-stats strong { color: var(--sodium); margin-right: 6px; font-weight: 700; }

/* ===== FILTER BAR ===== */
.filter-bar {
  max-width: 1280px; margin: 0 auto; padding: 28px 5vw 12px;
  display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
}
.filter-bar .filter-label {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.64rem; letter-spacing: 0.22em; text-transform: uppercase;
  color: var(--chalk-dim); margin-right: 8px;
}
.filter-chip {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.7rem; letter-spacing: 0.14em; text-transform: uppercase; font-weight: 600;
  background: transparent; color: var(--chalk-dim);
  border: 1px solid var(--grease);
  padding: 7px 13px;
  cursor: pointer;
  transition: all 0.15s ease;
}
.filter-chip:hover { border-color: var(--sodium); color: var(--chalk); }
.filter-chip[aria-pressed="true"] {
  background: var(--sodium); color: var(--asphalt); border-color: var(--sodium);
}
.filter-count {
  margin-left: auto;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.66rem; letter-spacing: 0.18em; text-transform: uppercase;
  color: var(--chalk-dim);
}
.filter-count strong { color: var(--sodium); }

/* ===== ROSTER GRID ===== */
.roster-grid {
  max-width: 1280px; margin: 0 auto; padding: 14px 5vw 80px;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 20px;
  list-style: none;
}

/* ===== HELP US BUILD THE ROSTER ===== */
.help-roster {
  max-width: 1280px; margin: 0 auto; padding: 50px 5vw 80px;
  border-top: 1px dashed var(--grease);
}
.help-inner { display: grid; gap: 32px; }
.help-head { max-width: 760px; }
.help-head .marker {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.72rem; letter-spacing: 0.24em; text-transform: uppercase;
  color: var(--race-red); font-weight: 700; margin-bottom: 12px;
  display: inline-flex; align-items: center; gap: 10px;
}
.help-head .marker::before {
  content: ""; width: 8px; height: 8px; background: var(--race-red);
  box-shadow: 0 0 6px var(--race-red);
}
.help-head h2 {
  font-family: 'Anton', sans-serif;
  font-size: clamp(1.8rem, 4vw, 2.8rem); line-height: 1.05;
  letter-spacing: 0.02em; margin-bottom: 14px;
}
.help-lede { color: var(--chalk-dim); font-size: 1.02rem; line-height: 1.6; }

.help-block { background: var(--asphalt-2); border: 1px solid var(--grease); padding: 26px 28px; }
.help-block h3 {
  font-family: 'Anton', sans-serif;
  font-size: 1.4rem; letter-spacing: 0.02em;
  color: var(--sodium); margin-bottom: 14px;
}
.help-sub { color: var(--chalk-dim); font-size: 0.92rem; margin-bottom: 14px; font-style: italic; }
.help-list {
  list-style: none; display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 10px 24px;
}
.help-list li {
  font-family: 'Space Grotesk', sans-serif; font-size: 0.96rem;
  display: flex; align-items: baseline; gap: 10px;
  padding: 8px 0; border-bottom: 1px dotted var(--grease);
}
.help-list.compact li { padding: 6px 0; font-size: 0.9rem; }
.help-list li .hn {
  font-family: 'JetBrains Mono', monospace; font-size: 0.78rem;
  letter-spacing: 0.05em; color: var(--race-red); font-weight: 700;
  min-width: 48px;
}
.help-list li strong { color: var(--chalk); font-weight: 600; }
.help-list li em {
  font-style: normal; font-family: 'JetBrains Mono', monospace;
  font-size: 0.7rem; letter-spacing: 0.08em; color: var(--chalk-dim);
  text-transform: uppercase; margin-left: auto;
}

.help-cta {
  background: linear-gradient(135deg, var(--race-red) 0%, #a01521 100%);
  padding: 32px 32px; text-align: center;
  display: flex; flex-direction: column; gap: 14px; align-items: center;
}
.help-btn {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.85rem; letter-spacing: 0.12em; text-transform: uppercase; font-weight: 700;
  background: var(--asphalt); color: var(--sodium);
  padding: 16px 28px; text-decoration: none; border: 2px solid var(--asphalt);
  transition: all 0.2s;
}
.help-btn:hover { background: var(--sodium); color: var(--asphalt); border-color: var(--sodium); }
.help-note {
  color: rgba(244, 237, 225, 0.9); font-size: 0.9rem;
  max-width: 600px; line-height: 1.5;
}
.help-note a { color: var(--sodium); text-decoration: none; border-bottom: 1px solid currentColor; }

.racer-card {
  position: relative;
  background: #050507;
  border: 1px solid var(--grease);
  overflow: hidden;
  cursor: pointer;
  transition: transform 0.25s ease, border-color 0.2s ease, box-shadow 0.25s ease;
  outline: none;
  display: block;
}
.racer-card:hover,
.racer-card:focus-visible {
  transform: translateY(-3px);
  border-color: var(--sodium);
  box-shadow: 0 12px 26px -10px rgba(255, 179, 25, 0.28);
}
.racer-card[hidden] { display: none; }

.racer-photo {
  position: relative;
  background: #000;
  aspect-ratio: 4 / 3;
  overflow: hidden;
}
.racer-photo img {
  width: 100%; height: 100%;
  object-fit: cover;
  display: block;
  transition: transform 0.5s ease, filter 0.25s ease;
}
.racer-card:hover .racer-photo img {
  transform: scale(1.04);
  filter: saturate(1.1) contrast(1.04);
}
/* Corner brackets — echo cinematic treatment from homepage */
.racer-photo .brackets span {
  position: absolute; width: 16px; height: 16px;
  border-color: var(--sodium);
  border-style: solid; border-width: 0;
  opacity: 0.9;
  z-index: 3;
}
.racer-photo .brackets span:nth-child(1) { top: 10px; left: 10px; border-top-width: 2px; border-left-width: 2px; }
.racer-photo .brackets span:nth-child(2) { top: 10px; right: 10px; border-top-width: 2px; border-right-width: 2px; }
.racer-photo .brackets span:nth-child(3) { bottom: 10px; left: 10px; border-bottom-width: 2px; border-left-width: 2px; }
.racer-photo .brackets span:nth-child(4) { bottom: 10px; right: 10px; border-bottom-width: 2px; border-right-width: 2px; }

/* Tag pill (top-left HUD) */
.racer-tag {
  position: absolute; top: 14px; left: 14px; z-index: 4;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.58rem; letter-spacing: 0.2em; text-transform: uppercase;
  color: var(--chalk); font-weight: 700;
  background: rgba(0,0,0,0.7);
  padding: 4px 8px;
  border-left: 2px solid var(--race-red);
}
.racer-tag.on-track { border-left-color: var(--sodium); }

/* Card footer */
.racer-meta {
  padding: 14px 16px 14px;
  background: linear-gradient(180deg, var(--asphalt-2) 0%, #131316 100%);
  border-top: 1px solid var(--grease);
  display: flex; align-items: baseline; gap: 14px;
}
.racer-num {
  font-family: 'Anton', sans-serif;
  font-size: 2.4rem; line-height: 0.85;
  color: var(--chalk); letter-spacing: -0.03em;
  text-shadow: 2px 2px 0 var(--race-red);
  min-width: 48px;
}
.racer-card.is-vince .racer-num { text-shadow: 2px 2px 0 var(--engine-blue); }
.racer-info { display: flex; flex-direction: column; gap: 3px; flex: 1; min-width: 0; }
.racer-name {
  font-family: 'Anton', sans-serif;
  font-size: 1rem; line-height: 1.05;
  text-transform: uppercase; letter-spacing: 0.02em;
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}
.racer-sub {
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.62rem; letter-spacing: 0.14em; text-transform: uppercase;
  color: var(--chalk-dim);
  white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

/* Spotlight Vince's #72 card (featured) */
.racer-card.is-featured {
  grid-column: span 2;
  border-color: var(--sodium);
}
.racer-card.is-featured .racer-photo { aspect-ratio: 16 / 9; }
.racer-card.is-featured::before {
  content: "FOUNDER-DRIVER"; position: absolute; top: 14px; right: 14px; z-index: 5;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.58rem; letter-spacing: 0.2em; font-weight: 700;
  color: var(--asphalt); background: var(--sodium);
  padding: 4px 8px;
}
@media (max-width: 620px) {
  .racer-card.is-featured { grid-column: span 1; }
  .racer-card.is-featured .racer-photo { aspect-ratio: 4 / 3; }
}

/* ===== LIGHTBOX ===== */
.lightbox {
  position: fixed; inset: 0;
  background: rgba(5,5,7,0.92);
  display: none; align-items: center; justify-content: center;
  z-index: 100; padding: 4vw;
  backdrop-filter: blur(8px);
  -webkit-backdrop-filter: blur(8px);
}
.lightbox[aria-hidden="false"] { display: flex; }
.lightbox-inner {
  max-width: 1280px; max-height: 90vh;
  display: flex; flex-direction: column; gap: 10px;
  width: 100%;
}
.lightbox-img-wrap {
  position: relative;
  background: #000;
  border: 1px solid var(--grease);
  overflow: hidden;
  max-height: 78vh;
}
.lightbox-img-wrap img {
  width: 100%; height: auto; max-height: 78vh;
  object-fit: contain;
  display: block;
}
.lightbox-caption {
  display: flex; justify-content: space-between; gap: 16px; flex-wrap: wrap;
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.7rem; letter-spacing: 0.14em; text-transform: uppercase;
  color: var(--chalk-dim);
}
.lightbox-caption .left { color: var(--sodium); font-weight: 700; }
.lightbox-close {
  position: absolute; top: 12px; right: 12px; z-index: 110;
  background: rgba(0,0,0,0.7); color: var(--chalk);
  border: 1px solid var(--sodium);
  font-family: 'JetBrains Mono', monospace;
  font-size: 0.8rem; letter-spacing: 0.14em;
  padding: 7px 12px;
  cursor: pointer;
}
.lightbox-close:hover { background: var(--sodium); color: var(--asphalt); }
.lightbox-nav {
  position: absolute; top: 50%; transform: translateY(-50%);
  background: rgba(0,0,0,0.7); color: var(--chalk);
  border: 1px solid var(--grease);
  padding: 14px 14px;
  font-family: 'JetBrains Mono', monospace; font-size: 1.1rem;
  cursor: pointer;
  line-height: 1;
}
.lightbox-nav:hover { border-color: var(--sodium); color: var(--sodium); }
.lightbox-prev { left: 14px; }
.lightbox-next { right: 14px; }

@media (prefers-reduced-motion: reduce) {
  *, *::before, *::after { animation-duration: 0.01ms !important; transition-duration: 0.01ms !important; }
  .racer-card:hover { transform: none; }
  .racer-card:hover .racer-photo img { transform: none; }
}
</style>

<?php
$body = <<<'VMRA_BODY_EOT'
<!-- ===== HERO ===== -->
<section class="roster-hero">
  <div class="marker">§ The Roster · 2026 Season</div>
  <h1>The Cars.<br>The <span class="accent">Drivers.</span><br>The Field.</h1>
  <p class="lede">These cars don't roll off a truck. They get built. Tube chassis. Fiberglass body. Small-block V8. Nothing on it that didn't get put there by the owner or someone who owed the owner a favor. <strong>This is what 40 years of vintage modified racing actually looks like</strong> — a car in a shop, a sponsor on the door, a number on the roof, a memory on the deck.</p>
  <div class="roster-stats">
    <span><strong id="countTotal">15</strong> photos</span>
    <span><strong id="countPaddock">11</strong> paddock</span>
    <span><strong id="countRace">4</strong> on-track</span>
    <span><strong>40th</strong> season</span>
  </div>
</section>

<!-- ===== FILTER CHIPS ===== -->
<div class="filter-bar" role="toolbar" aria-label="Filter roster photos">
  <span class="filter-label">Filter:</span>
  <button class="filter-chip" data-filter="all" aria-pressed="true">All</button>
  <button class="filter-chip" data-filter="paddock" aria-pressed="false">Paddock</button>
  <button class="filter-chip" data-filter="track" aria-pressed="false">On Track</button>
  <button class="filter-chip" data-filter="vince" aria-pressed="false">Vince #72 & #82</button>
  <button class="filter-chip" data-filter="modified" aria-pressed="false">Modified</button>
  <button class="filter-chip" data-filter="legends" aria-pressed="false">Legends</button>
  <span class="filter-count"><strong id="visibleCount">15</strong> shown</span>
</div>

<!-- ===== ROSTER GRID ===== -->
<ul class="roster-grid" id="rosterGrid" role="list">
  <!-- Cards injected by script below -->
</ul>

<!-- ===== HELP US BUILD THE ROSTER ===== -->
<section class="help-roster" aria-label="Drivers we still need photos of">
  <div class="help-inner">
    <div class="help-head">
      <span class="marker">§ Help Us Finish the Wall</span>
      <h2>Drivers We Still Need on the Page.</h2>
      <p class="help-lede">Forty years of VMRA, and the website doesn't have everybody yet. If you race one of these numbers — or you've got a phone full of paddock shots from past seasons — send us what you've got. One paddock photo and one on-track shot, that's it. We'll handle the rest.</p>
    </div>

    <div class="help-block">
      <h3>Top of the Standings · 2026 YTD after R01</h3>
      <ul class="help-list">
        <li><span class="hn">#23</span> <strong>Kahl Cheth</strong> <em>2026 leader · 64 pts</em></li>
        <li><span class="hn">#8</span> <strong>Jason Quatsoe</strong> <em>2nd · 60 pts</em></li>
        <li><span class="hn">#22</span> <strong>Steve Woods</strong> <em>3rd · 57 pts</em></li>
        <li><span class="hn">#68</span> <strong>B. Hector Sr</strong> <em>4th · 55 pts · rookie leader</em></li>
        <li><span class="hn">#57</span> <strong>Shane Strimple</strong> <em>5th · 53 pts</em></li>
        <li><span class="hn">#82</span> <strong>Vince Conwell</strong> <em>7th · 50 pts</em></li>
      </ul>
    </div>

    <div class="help-block">
      <h3>Mid-Pack Regulars</h3>
      <ul class="help-list">
        <li><span class="hn">#23x</span> <strong>Chad Broom</strong></li>
        <li><span class="hn">#18</span> <strong>Dom Hunter</strong></li>
        <li><span class="hn">#10</span> <strong>B. Cottrell / G. Scott</strong></li>
        <li><span class="hn">#69</span> <strong>B. Ohler</strong></li>
      </ul>
    </div>

    <div class="help-block">
      <h3>The Rest of the 2026 Bios List</h3>
      <ul class="help-list">
        <li><span class="hn">#2</span> <strong>Rick Villyard</strong></li>
        <li><span class="hn">#3</span> <strong>Jim Jones</strong></li>
        <li><span class="hn">#15</span> <strong>Ed Beck</strong></li>
        <li><span class="hn">#54</span> <strong>Aaron Clother</strong></li>
        <li><span class="hn">#77</span> <strong>Vern Huson</strong></li>
        <li><span class="hn">#88</span> <strong>Robert Rux</strong></li>
        <li><span class="hn">#92</span> <strong>Todd McCartney</strong></li>
      </ul>
    </div>

    <div class="help-block">
      <h3>Cars on the Page, Driver Not Confirmed</h3>
      <p class="help-sub">Photos are up, but we don't know who drives them. Recognize one? Tell us.</p>
      <ul class="help-list compact">
        <li><span class="hn">#02</span> V Construction &amp; Remodel</li>
        <li><span class="hn">#08</span> Stars-&amp;-Stripes Legend</li>
        <li><span class="hn">#25</span> Johnson Electric Northwest</li>
        <li><span class="hn">#29</span> Side-by-side with Cheater Dave</li>
        <li><span class="hn">#68</span> Maroon &amp; yellow modified</li>
        <li><span class="hn">#79</span> Red/white/blue legend</li>
        <li><span class="hn">—</span> White Sportsman · Central Rescue Supply</li>
      </ul>
    </div>

    <div class="help-cta">
      <a href="mailto:board@vmra.club?subject=Roster%20Photo%20Submission&body=Car%20Number:%20%0ADriver%20Name:%20%0ASponsors:%20%0AHometown:%20%0AYears%20Racing:%20%0AOne-line%20bio:%20%0A%0A%5BAttach%20one%20paddock%20photo%20and%20one%20on-track%20photo%5D" class="help-btn">Send Us Your Photos →</a>
      <p class="help-note">Phone shots are fine. Put the car number in the subject. We'll resize, crop, and post within a few days. Email <a href="mailto:board@vmra.club">board@vmra.club</a>.</p>
    </div>
  </div>
</section>

<!-- ===== LIGHTBOX ===== -->
<div class="lightbox" id="lightbox" aria-hidden="true" role="dialog" aria-modal="true" aria-label="Enlarged photograph">
  <div class="lightbox-inner">
    <div class="lightbox-img-wrap">
      <button class="lightbox-close" id="lbClose" aria-label="Close lightbox">✕ Close</button>
      <button class="lightbox-nav lightbox-prev" id="lbPrev" aria-label="Previous photo">‹</button>
      <button class="lightbox-nav lightbox-next" id="lbNext" aria-label="Next photo">›</button>
      <img id="lbImg" src="" alt="">
    </div>
    <div class="lightbox-caption">
      <span class="left" id="lbTitle">#72 · Vince Conwell</span>
      <span id="lbSub">Paddock · Northwest Concrete Cutting</span>
    </div>
  </div>
</div>

<!-- ===== FOOTER ===== -->
VMRA_BODY_EOT;

// Retarget /data/*.json fetches at the theme's data dir.
$body = str_replace( "'/data/", "'" . $vmra_data_base . "/", $body );
$body = str_replace( '"/data/', '"' . $vmra_data_base . '/', $body );
echo $body;
?>

<?php get_footer();
