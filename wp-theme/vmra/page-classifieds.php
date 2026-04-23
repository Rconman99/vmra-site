<?php
/**
 * Template for the /classifieds/ page.
 * Ported from the static public/classifieds/index.html.
 *
 * WP auto-loads this template when a Page with slug "classifieds" is viewed.
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

.hero{padding:50px 5vw 30px;border-bottom:1px solid var(--grease);background:linear-gradient(180deg,var(--asphalt-2),var(--asphalt))}
.hero-inner{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1fr auto;gap:30px;align-items:end}
.eyebrow{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-size:.78rem;letter-spacing:.2em;text-transform:uppercase;margin-bottom:14px}
h1{font-family:'Anton',sans-serif;font-size:clamp(2.2rem,5vw,3.6rem);letter-spacing:.02em;line-height:1;margin-bottom:14px}
.lede{color:var(--chalk-dim);max-width:680px;font-size:1.05rem}
.hero-cta{font-family:'JetBrains Mono',monospace;font-size:.78rem;letter-spacing:.12em;text-transform:uppercase;background:var(--sodium);color:var(--asphalt);padding:14px 22px;text-decoration:none;font-weight:700;display:inline-block;border:2px solid var(--sodium);transition:all .2s;white-space:nowrap}
.hero-cta:hover{background:transparent;color:var(--sodium)}
@media (max-width:760px){.hero-inner{grid-template-columns:1fr}}

/* CONTROLS BAR — search + sort */
.controls{background:var(--asphalt-2);border-bottom:1px solid var(--grease);padding:18px 5vw;position:sticky;top:62px;z-index:40}
.controls-inner{max-width:1280px;margin:0 auto;display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.search-wrap{flex:1;min-width:240px;position:relative}
.search{width:100%;background:var(--asphalt);border:1px solid var(--grease);color:var(--chalk);padding:10px 14px 10px 38px;font-family:'Space Grotesk',sans-serif;font-size:.95rem;border-radius:2px}
.search:focus{outline:none;border-color:var(--race-red)}
.search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--chalk-dim);font-size:1rem}
.sort-wrap{display:flex;align-items:center;gap:8px;font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.08em;color:var(--chalk-dim);text-transform:uppercase}
.sort-select{background:var(--asphalt);border:1px solid var(--grease);color:var(--chalk);padding:8px 12px;font-family:'JetBrains Mono',monospace;font-size:.78rem;cursor:pointer}

/* CATEGORY CHIPS */
.chips{padding:18px 5vw;border-bottom:1px solid var(--grease);background:var(--asphalt)}
.chips-inner{max-width:1280px;margin:0 auto;display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.chip-label{font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.15em;text-transform:uppercase;color:var(--sodium);margin-right:4px}
.chip{font-family:'JetBrains Mono',monospace;font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;background:transparent;border:1px solid var(--grease);color:var(--chalk-dim);padding:8px 14px;cursor:pointer;transition:all .15s;border-radius:2px}
.chip:hover{border-color:var(--chalk-dim);color:var(--chalk)}
.chip[aria-pressed="true"]{background:var(--race-red);border-color:var(--race-red);color:var(--chalk);font-weight:700}
.count{font-family:'JetBrains Mono',monospace;font-size:.78rem;color:var(--chalk-dim);margin-left:auto}

/* LISTINGS GRID */
main{max-width:1280px;margin:0 auto;padding:40px 5vw}
.listings{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px}
.listing{background:var(--asphalt-2);border:1px solid var(--grease);text-decoration:none;color:inherit;display:flex;flex-direction:column;transition:all .2s;position:relative;overflow:hidden}
.listing:hover{border-color:var(--race-red);transform:translateY(-3px)}
.listing-photo{aspect-ratio:4/3;background:var(--asphalt-3) repeating-linear-gradient(45deg,transparent 0,transparent 12px,rgba(255,255,255,.02) 12px,rgba(255,255,255,.02) 24px);display:flex;align-items:center;justify-content:center;color:var(--chalk-dim);font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.15em;text-transform:uppercase;border-bottom:1px solid var(--grease);position:relative}
.listing-photo .ph-icon{font-family:'Anton',sans-serif;font-size:2.6rem;color:var(--grease);letter-spacing:.04em}
.listing-tag{position:absolute;top:10px;left:10px;font-family:'JetBrains Mono',monospace;font-size:.65rem;letter-spacing:.12em;text-transform:uppercase;background:var(--race-red);color:var(--chalk);padding:5px 10px;border-radius:2px;font-weight:700}
.listing-tag.example{background:var(--engine-blue)}
.listing-tag.sold{background:#444;color:var(--chalk-dim)}
.listing-tag.new{background:var(--sodium);color:var(--asphalt)}
.listing-body{padding:18px 20px 20px;flex:1;display:flex;flex-direction:column}
.listing-cat{font-family:'JetBrains Mono',monospace;font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:var(--sodium);margin-bottom:8px}
.listing-title{font-family:'Anton',sans-serif;font-size:1.25rem;line-height:1.15;margin-bottom:8px}
.listing-desc{color:var(--chalk-dim);font-size:.9rem;margin-bottom:16px;flex:1}
.listing-foot{display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid var(--grease)}
.listing-price{font-family:'Anton',sans-serif;font-size:1.4rem;color:var(--sodium);letter-spacing:.02em}
.listing-price.obo{font-size:1rem}
.listing-meta{font-family:'JetBrains Mono',monospace;font-size:.7rem;color:var(--chalk-dim);text-align:right}
.listing-meta div+div{margin-top:3px}

/* EMPTY STATE */
.empty-state{grid-column:1/-1;background:var(--asphalt-2);border:2px dashed var(--grease);padding:60px 30px;text-align:center;display:none}
.empty-state.show{display:block}
.empty-state h3{font-family:'Anton',sans-serif;font-size:1.6rem;margin-bottom:10px}
.empty-state p{color:var(--chalk-dim);max-width:520px;margin:0 auto 20px}

/* SUBMIT CTA SECTION */
.submit-strip{background:linear-gradient(135deg,var(--race-red) 0%,#a01521 100%);padding:50px 5vw;margin-top:60px;border-top:1px solid var(--grease);border-bottom:1px solid var(--grease)}
.submit-inner{max-width:1280px;margin:0 auto;display:grid;grid-template-columns:1fr auto;gap:30px;align-items:center}
.submit-strip h2{font-family:'Anton',sans-serif;font-size:clamp(1.8rem,4vw,2.6rem);line-height:1.05;letter-spacing:.02em;margin-bottom:8px}
.submit-strip p{color:rgba(244,237,225,.9);font-size:1.02rem;max-width:680px}
.submit-btn{font-family:'JetBrains Mono',monospace;font-size:.85rem;letter-spacing:.12em;text-transform:uppercase;background:var(--asphalt);color:var(--sodium);padding:18px 28px;text-decoration:none;font-weight:700;display:inline-block;border:2px solid var(--asphalt);transition:all .2s;white-space:nowrap}
.submit-btn:hover{background:var(--sodium);color:var(--asphalt);border-color:var(--sodium)}
@media (max-width:760px){.submit-inner{grid-template-columns:1fr}}

/* HOW IT WORKS */
.how{padding:60px 5vw}
.how-inner{max-width:1280px;margin:0 auto}
.how h2{font-family:'Anton',sans-serif;font-size:clamp(1.6rem,3.5vw,2.4rem);line-height:1.1;margin-bottom:30px}
.how-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(240px,1fr));gap:24px}
.how-step{background:var(--asphalt-2);padding:26px;border-left:3px solid var(--race-red)}
.step-num{font-family:'JetBrains Mono',monospace;font-size:.72rem;color:var(--sodium);letter-spacing:.15em;text-transform:uppercase;margin-bottom:8px}
.step-title{font-family:'Anton',sans-serif;font-size:1.3rem;line-height:1.1;margin-bottom:8px}
.step-desc{color:var(--chalk-dim);font-size:.92rem}
</style>

<?php
$body = <<<'VMRA_BODY_EOT'
<section class="hero"><div class="hero-inner">
  <div>
    <span class="eyebrow">§ Classifieds · Members &amp; Friends</span>
    <h1>Cars, Engines, Parts.<br>Between People Who Race Them.</h1>
    <p class="lede">Free classifieds board for vintage modified equipment in the Pacific Northwest — cars, crate motors, takeoff Hoosiers, gauges, scales, anything that lives in a race shop. No fees, no commission, no signup. Email a photo and an asking price. We'll post it within 48 hours. When it sells, reply with "SOLD" and we'll flip the badge.</p>
  </div>
  <a class="hero-cta" href="mailto:board@vmra.club?subject=Classified%20Listing%20Submission&body=Title:%20%0AAsking%20Price:%20%0ALocation:%20%0ACondition:%20%0ADescription:%20%0AContact%20number:%20%0A%0A%5BAttach%201-3%20photos%20to%20this%20email%5D">Submit a Listing →</a>
</div></section>

<section class="controls"><div class="controls-inner">
  <div class="search-wrap">
    <span class="search-icon">⌕</span>
    <input class="search" id="searchBox" type="text" placeholder="Search title, description, or part type…" aria-label="Search listings">
  </div>
  <div class="sort-wrap">
    <span>Sort:</span>
    <select id="sortSelect" class="sort-select">
      <option value="newest">Newest first</option>
      <option value="oldest">Oldest first</option>
      <option value="price-low">Price: low → high</option>
      <option value="price-high">Price: high → low</option>
    </select>
  </div>
</div></section>

<section class="chips"><div class="chips-inner">
  <span class="chip-label">Browse:</span>
  <button class="chip" data-filter="all" aria-pressed="true">All</button>
  <button class="chip" data-filter="cars" aria-pressed="false">Race Cars</button>
  <button class="chip" data-filter="engines" aria-pressed="false">Engines</button>
  <button class="chip" data-filter="parts" aria-pressed="false">Parts</button>
  <button class="chip" data-filter="trailers" aria-pressed="false">Trailers</button>
  <button class="chip" data-filter="tools" aria-pressed="false">Tools / Shop</button>
  <button class="chip" data-filter="wanted" aria-pressed="false">Wanted</button>
  <span class="count" id="resultCount"></span>
</div></section>

<main>
  <div class="listings" id="listingsGrid">
    <!-- Listings injected by JS below -->
    <div class="empty-state" id="emptyState">
      <h3>No active listings match that filter.</h3>
      <p>Be the first to post a car, engine, or part. Email <a href="mailto:board@vmra.club" style="color:var(--sodium)">board@vmra.club</a> with your listing details and photos — we'll get it up within 48 hours.</p>
      <a class="hero-cta" style="background:var(--race-red);color:var(--chalk);border-color:var(--race-red)" href="mailto:board@vmra.club?subject=Classified%20Listing%20Submission">Submit a Listing →</a>
    </div>
  </div>
</main>

<section class="submit-strip"><div class="submit-inner">
  <div>
    <h2>Got Something to Sell?</h2>
    <p>Member or not — if it's vintage modified equipment, we'll list it. Cars, engines, body panels, trailers, gauges, scales, tire racks, anything. No commission, no listing fee, no time limit. We'll mark it SOLD when you tell us.</p>
  </div>
  <a class="submit-btn" href="mailto:board@vmra.club?subject=Classified%20Listing%20Submission&body=Title:%20%0AAsking%20Price:%20%0ALocation:%20%0ACondition:%20%0ADescription:%20%0AContact%20number:%20%0A%0A%5BAttach%201-3%20photos%20to%20this%20email%5D">Email Your Listing →</a>
</div></section>

<section class="how"><div class="how-inner">
  <h2>How It Works.</h2>
  <div class="how-grid">
    <div class="how-step">
      <div class="step-num">Step 01</div>
      <div class="step-title">Email Your Listing</div>
      <div class="step-desc">Send the title, asking price, location, condition, description, and your contact number to <a href="mailto:board@vmra.club" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor">board@vmra.club</a>. Attach 1-3 photos.</div>
    </div>
    <div class="how-step">
      <div class="step-num">Step 02</div>
      <div class="step-title">We Post It</div>
      <div class="step-desc">Within 48 hours your listing goes live with a "NEW" badge. Stays at the top of the grid for the first 7 days.</div>
    </div>
    <div class="how-step">
      <div class="step-num">Step 03</div>
      <div class="step-title">Buyers Contact You Directly</div>
      <div class="step-desc">No middleman. The board doesn't broker the deal — your phone or email is on the listing, and buyers reach you directly.</div>
    </div>
    <div class="how-step">
      <div class="step-num">Step 04</div>
      <div class="step-title">Mark It Sold</div>
      <div class="step-desc">Reply to your listing email with "SOLD" and we'll flip the badge. Listings stay archived for the rest of the season for reference.</div>
    </div>
  </div>
</div></section>
VMRA_BODY_EOT;

// Retarget /data/*.json fetches at the theme's data dir.
$body = str_replace( "'/data/", "'" . $vmra_data_base . "/", $body );
$body = str_replace( '"/data/', '"' . $vmra_data_base . '/', $body );
echo $body;
?>

<?php get_footer();
