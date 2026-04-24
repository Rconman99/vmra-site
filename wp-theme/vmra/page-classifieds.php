<?php
/**
 * Template for the /classifieds/ page.
 *
 * v1.4 — self-service: renders live listings from the vmra_classified CPT
 * and provides a public submission form. REST endpoint at
 * /wp-json/vmra/v1/classified handles the POST.
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

$vmra_data_base = esc_url( VMRA_THEME_URI . '/data' );
$classifieds_q = new WP_Query( array(
	'post_type'      => 'vmra_classified',
	'post_status'    => 'publish',
	'posts_per_page' => 60,
	'orderby'        => 'date',
	'order'          => 'DESC',
) );

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
.hero-cta{font-family:'JetBrains Mono',monospace;font-size:.78rem;letter-spacing:.12em;text-transform:uppercase;background:var(--sodium);color:var(--asphalt);padding:14px 22px;text-decoration:none;font-weight:700;display:inline-block;border:2px solid var(--sodium);transition:all .2s;white-space:nowrap;cursor:pointer}
.hero-cta:hover{background:transparent;color:var(--sodium)}
@media (max-width:760px){.hero-inner{grid-template-columns:1fr}}

.controls{background:var(--asphalt-2);border-bottom:1px solid var(--grease);padding:18px 5vw;position:sticky;top:62px;z-index:40}
.controls-inner{max-width:1280px;margin:0 auto;display:flex;gap:14px;align-items:center;flex-wrap:wrap}
.search-wrap{flex:1;min-width:240px;position:relative}
.search{width:100%;background:var(--asphalt);border:1px solid var(--grease);color:var(--chalk);padding:10px 14px 10px 38px;font-family:'Space Grotesk',sans-serif;font-size:.95rem;border-radius:2px}
.search:focus{outline:none;border-color:var(--race-red)}
.search-icon{position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--chalk-dim);font-size:1rem}
.sort-wrap{display:flex;align-items:center;gap:8px;font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.08em;color:var(--chalk-dim);text-transform:uppercase}
.sort-select{background:var(--asphalt);border:1px solid var(--grease);color:var(--chalk);padding:8px 12px;font-family:'JetBrains Mono',monospace;font-size:.78rem;cursor:pointer}

.chips{padding:18px 5vw;border-bottom:1px solid var(--grease);background:var(--asphalt)}
.chips-inner{max-width:1280px;margin:0 auto;display:flex;gap:10px;flex-wrap:wrap;align-items:center}
.chip-label{font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.15em;text-transform:uppercase;color:var(--sodium);margin-right:4px}
.chip{font-family:'JetBrains Mono',monospace;font-size:.78rem;letter-spacing:.06em;text-transform:uppercase;background:transparent;border:1px solid var(--grease);color:var(--chalk-dim);padding:8px 14px;cursor:pointer;transition:all .15s;border-radius:2px}
.chip:hover{border-color:var(--chalk-dim);color:var(--chalk)}
.chip[aria-pressed="true"]{background:var(--race-red);border-color:var(--race-red);color:var(--chalk);font-weight:700}
.count{font-family:'JetBrains Mono',monospace;font-size:.78rem;color:var(--chalk-dim);margin-left:auto}
</style>

<style>
main.cl-main{max-width:1280px;margin:0 auto;padding:40px 5vw}
.listings{display:grid;grid-template-columns:repeat(auto-fill,minmax(320px,1fr));gap:20px}
.listing{background:var(--asphalt-2);border:1px solid var(--grease);text-decoration:none;color:inherit;display:flex;flex-direction:column;transition:all .2s;position:relative;overflow:hidden}
.listing:hover{border-color:var(--race-red);transform:translateY(-3px)}
.listing-photo{aspect-ratio:4/3;background:var(--asphalt-3) repeating-linear-gradient(45deg,transparent 0,transparent 12px,rgba(255,255,255,.02) 12px,rgba(255,255,255,.02) 24px);display:flex;align-items:center;justify-content:center;color:var(--chalk-dim);font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.15em;text-transform:uppercase;border-bottom:1px solid var(--grease);position:relative;overflow:hidden}
.listing-photo img{width:100%;height:100%;object-fit:cover;display:block}
.listing-photo .ph-icon{font-family:'Anton',sans-serif;font-size:2.6rem;color:var(--grease);letter-spacing:.04em}
.listing-tag{position:absolute;top:10px;left:10px;font-family:'JetBrains Mono',monospace;font-size:.65rem;letter-spacing:.12em;text-transform:uppercase;background:var(--sodium);color:var(--asphalt);padding:5px 10px;border-radius:2px;font-weight:700;z-index:2}
.listing-tag.sold{background:#444;color:var(--chalk-dim)}
.listing-body{padding:18px 20px 20px;flex:1;display:flex;flex-direction:column}
.listing-cat{font-family:'JetBrains Mono',monospace;font-size:.68rem;letter-spacing:.15em;text-transform:uppercase;color:var(--sodium);margin-bottom:8px}
.listing-title{font-family:'Anton',sans-serif;font-size:1.25rem;line-height:1.15;margin-bottom:8px}
.listing-desc{color:var(--chalk-dim);font-size:.9rem;margin-bottom:16px;flex:1;display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
.listing-foot{display:flex;justify-content:space-between;align-items:center;padding-top:14px;border-top:1px solid var(--grease)}
.listing-price{font-family:'Anton',sans-serif;font-size:1.4rem;color:var(--sodium);letter-spacing:.02em}
.listing-meta{font-family:'JetBrains Mono',monospace;font-size:.7rem;color:var(--chalk-dim);text-align:right}
.listing.is-sold{opacity:.6}
.listing.is-sold .listing-price{color:var(--chalk-dim);text-decoration:line-through}

.empty-state{grid-column:1/-1;background:var(--asphalt-2);border:2px dashed var(--grease);padding:60px 30px;text-align:center}
.empty-state h3{font-family:'Anton',sans-serif;font-size:1.6rem;margin-bottom:10px}
.empty-state p{color:var(--chalk-dim);max-width:520px;margin:0 auto 20px}

/* Submit-item form section */
.list-section{background:linear-gradient(135deg,var(--race-red) 0%,#a01521 100%);padding:60px 5vw;margin-top:40px;border-top:1px solid var(--grease);border-bottom:1px solid var(--grease)}
.list-inner{max-width:1000px;margin:0 auto}
.list-header{margin-bottom:30px}
.list-header h2{font-family:'Anton',sans-serif;font-size:clamp(1.8rem,4vw,2.6rem);line-height:1.05;letter-spacing:.02em;margin-bottom:10px}
.list-header p{color:rgba(244,237,225,.9);font-size:1.02rem;max-width:680px}
.cl-form{background:var(--asphalt);border:1px solid var(--grease);padding:28px;display:grid;grid-template-columns:1fr 1fr;gap:16px 20px}
.cl-form .full{grid-column:1/-1}
.cl-form label{font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.12em;text-transform:uppercase;color:var(--sodium);display:block;margin-bottom:6px}
.cl-form input[type=text],.cl-form input[type=email],.cl-form input[type=tel],.cl-form select,.cl-form textarea{width:100%;background:var(--asphalt-2);border:1px solid var(--grease);color:var(--chalk);padding:10px 12px;font-family:'Space Grotesk',sans-serif;font-size:.95rem;border-radius:2px}
.cl-form textarea{min-height:110px;resize:vertical;font-family:inherit}
.cl-form input:focus,.cl-form select:focus,.cl-form textarea:focus{outline:none;border-color:var(--race-red)}
.cl-form .toggle-row{display:flex;gap:20px;flex-wrap:wrap;align-items:center;color:var(--chalk);font-size:.9rem}
.cl-form .toggle-row label{color:var(--chalk);font-family:'Space Grotesk',sans-serif;font-size:.88rem;letter-spacing:normal;text-transform:none;display:inline-flex;align-items:center;gap:8px;cursor:pointer;margin:0}
.cl-form .toggle-row input[type=checkbox]{width:18px;height:18px;accent-color:var(--race-red)}
.cl-form .hint{color:var(--chalk-dim);font-size:.78rem;margin-top:4px}
.cl-form .honeypot{position:absolute;left:-9999px;opacity:0;pointer-events:none;height:0;width:0}
.cl-submit{font-family:'JetBrains Mono',monospace;font-size:.85rem;letter-spacing:.12em;text-transform:uppercase;background:var(--sodium);color:var(--asphalt);padding:16px 28px;border:2px solid var(--sodium);font-weight:700;cursor:pointer;transition:all .2s}
.cl-submit:hover:not(:disabled){background:transparent;color:var(--sodium)}
.cl-submit:disabled{opacity:.5;cursor:wait}
.cl-form-msg{grid-column:1/-1;padding:14px 18px;border:1px solid var(--grease);background:var(--asphalt-2);font-size:.92rem;display:none}
.cl-form-msg.ok{border-color:#2a8f3e;color:#8fd09e;display:block}
.cl-form-msg.err{border-color:var(--race-red);color:#ff8a94;display:block}
@media (max-width:760px){.cl-form{grid-template-columns:1fr}}
</style>

<style>
/* FAQ section (kept from v1.3.9) */
.faq{padding:60px 5vw;border-top:1px solid var(--grease);background:var(--asphalt-2)}
.faq-inner{max-width:1280px;margin:0 auto}
.faq .eyebrow{display:block;margin-bottom:14px}
.faq h2{font-family:'Anton',sans-serif;font-size:clamp(1.8rem,4vw,2.6rem);letter-spacing:.02em;line-height:1.05;margin-bottom:36px}
.faq-grid{display:grid;grid-template-columns:repeat(2,1fr);gap:28px}
@media (max-width:760px){.faq-grid{grid-template-columns:1fr;gap:22px}}
.faq-item{background:var(--asphalt-3);border:1px solid var(--grease);padding:24px 26px;transition:border-color .2s}
.faq-item:hover{border-color:var(--race-red)}
.faq-item h3{font-family:'Anton',sans-serif;font-size:1.15rem;letter-spacing:.02em;line-height:1.15;margin-bottom:10px;color:var(--chalk)}
.faq-item p{font-size:.98rem;line-height:1.55;color:var(--chalk-dim)}
.faq-item a{color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor}
</style>

<main id="main-content" class="cl-main-wrap">

<?php if ( isset( $_GET['submitted'] ) && $_GET['submitted'] === '1' ) : ?>
<div class="cl-submit-success" role="status" aria-live="polite" style="background:linear-gradient(135deg,#1f6b2a 0%,#0f3a17 100%);border-top:1px solid #2a2a30;border-bottom:3px solid var(--sodium,#ffb319);padding:22px 5vw;color:#f4ede1;">
	<div style="max-width:1080px;margin:0 auto;display:flex;gap:20px;align-items:center;flex-wrap:wrap">
		<div style="font-family:'Anton',sans-serif;font-size:2.2rem;line-height:1;color:var(--sodium,#ffb319)">✓</div>
		<div style="flex:1;min-width:260px">
			<div style="font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.2em;text-transform:uppercase;color:var(--sodium,#ffb319);margin-bottom:6px">§ Submitted for Board Approval</div>
			<div style="font-family:'Anton',sans-serif;font-size:1.4rem;line-height:1.1;margin-bottom:4px">Thanks — your listing is in the queue.</div>
			<div style="font-size:.95rem;color:#d8cfbd">The VMRA board reviews every submission before it goes live (usually within 24 hours). You will get an email at the address you provided the moment it is approved. Check your spam folder if you don't see it.</div>
		</div>
	</div>
</div>
<?php endif; ?>

<section class="hero"><div class="hero-inner">
	<div>
		<span class="eyebrow">§ Classifieds · Members &amp; Friends</span>
		<h1>Cars, Engines, Parts.<br>Between People Who Race Them.</h1>
		<p class="lede">The VMRA Classifieds is a free, no-signup board where Pacific Northwest vintage modified racers buy and sell directly — no fees, no commission, no middleman. List a car, engine, or part in 60 seconds. Every submission is reviewed by the VMRA board before going live, typically within 24 hours.</p>
	</div>
	<a class="hero-cta" href="#list-item">List an Item →</a>
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

<main class="cl-main">
	<div class="listings" id="listingsGrid">

<?php if ( $classifieds_q->have_posts() ) : ?>
	<?php while ( $classifieds_q->have_posts() ) : $classifieds_q->the_post(); ?>
		<?php
		$m         = vmra_classified_meta();
		$sold      = ! empty( $m['sold_at'] );
		$excerpt   = wp_trim_words( wp_strip_all_tags( get_the_content() ), 25, '…' );
		$posted_ts = get_post_timestamp();
		?>
		<a class="listing <?php echo $sold ? 'is-sold' : ''; ?>"
		   href="<?php the_permalink(); ?>"
		   data-category="<?php echo esc_attr( $m['category'] ); ?>"
		   data-title="<?php echo esc_attr( strtolower( get_the_title() ) ); ?>"
		   data-desc="<?php echo esc_attr( strtolower( $excerpt ) ); ?>"
		   data-price="<?php echo esc_attr( (float) preg_replace( '/[^0-9.]/', '', $m['price'] ) ); ?>"
		   data-posted="<?php echo esc_attr( (int) $posted_ts ); ?>">
			<div class="listing-photo">
				<?php if ( $m['primary_photo'] ) : ?>
					<img src="<?php echo esc_url( $m['primary_photo'] ); ?>" alt="<?php the_title_attribute(); ?>" loading="lazy">
				<?php else : ?>
					<span class="ph-icon">VMRA</span>
				<?php endif; ?>
				<?php if ( $sold ) : ?>
					<span class="listing-tag sold">Sold</span>
				<?php elseif ( ( time() - (int) $posted_ts ) < 7 * DAY_IN_SECONDS ) : ?>
					<span class="listing-tag">New</span>
				<?php endif; ?>
			</div>
			<div class="listing-body">
				<?php if ( $m['category'] ) : ?>
					<div class="listing-cat"><?php echo esc_html( ucfirst( $m['category'] ) ); ?></div>
				<?php endif; ?>
				<div class="listing-title"><?php the_title(); ?></div>
				<div class="listing-desc"><?php echo esc_html( $excerpt ); ?></div>
				<div class="listing-foot">
					<div class="listing-price"><?php echo $m['price'] ? esc_html( $m['price'] ) : '—'; ?></div>
					<div class="listing-meta">
						<div><?php echo esc_html( human_time_diff( $posted_ts, current_time( 'timestamp' ) ) ); ?> ago</div>
						<?php if ( $m['location'] ) : ?><div><?php echo esc_html( $m['location'] ); ?></div><?php endif; ?>
					</div>
				</div>
			</div>
		</a>
	<?php endwhile; wp_reset_postdata(); ?>
<?php else : ?>
	<div class="empty-state">
		<h3>No active listings yet.</h3>
		<p>Be the first to post a car, engine, or part. Use the form below — the board reviews each submission before it goes live (usually within 24 hours). You get an email the moment it is approved.</p>
		<a class="hero-cta" href="#list-item">List an Item →</a>
	</div>
<?php endif; ?>

	</div>
</main>

<section class="list-section" id="list-item"><div class="list-inner">
	<div class="list-header">
		<h2>List an Item · 60 Seconds · Free</h2>
		<p>Every submission is reviewed by the VMRA board before it goes public — typically within 24 hours. You will get an email the moment your listing is approved. No signup, no fees, no commission.</p>
	</div>
	<form class="cl-form" id="vmraClassifiedForm" enctype="multipart/form-data" novalidate>
		<div class="honeypot" aria-hidden="true">
			<label for="cf-website">Website</label>
			<input type="text" id="cf-website" name="website" tabindex="-1" autocomplete="off">
		</div>

		<div class="full">
			<label for="cf-title">Listing title *</label>
			<input type="text" id="cf-title" name="title" required maxlength="80" placeholder="e.g. 1965 Chevelle vintage modified — turnkey">
		</div>

		<div>
			<label for="cf-category">Category *</label>
			<select id="cf-category" name="category" required>
				<option value="">— pick one —</option>
				<option value="cars">Race Cars</option>
				<option value="engines">Engines</option>
				<option value="parts">Parts</option>
				<option value="trailers">Trailers</option>
				<option value="tools">Tools / Shop</option>
				<option value="wanted">Wanted</option>
			</select>
		</div>

		<div>
			<label for="cf-price">Price *</label>
			<input type="text" id="cf-price" name="price" required placeholder="$850 · $1,200 OBO · Trade">
		</div>

		<div>
			<label for="cf-condition">Condition</label>
			<select id="cf-condition" name="condition">
				<option value="">—</option>
				<option value="new">New</option>
				<option value="like-new">Used · like new</option>
				<option value="good">Used · good</option>
				<option value="fair">Used · fair</option>
				<option value="parts">For parts</option>
			</select>
		</div>

		<div>
			<label for="cf-location">Location</label>
			<input type="text" id="cf-location" name="location" placeholder="Monroe, WA">
		</div>

		<div class="full">
			<label for="cf-description">Description *</label>
			<textarea id="cf-description" name="description" required maxlength="3000" placeholder="What it is, what shape it's in, what's included. 20 characters minimum. Links will be stripped."></textarea>
			<div class="hint">Don't include a phone number or email here — use the fields below. URLs get stripped automatically.</div>
		</div>

		<div class="full">
			<label for="cf-photos">Photos (optional — up to 3 · JPEG / PNG / WebP · 5MB each)</label>
			<input type="file" id="cf-photos" name="photos" multiple accept="image/jpeg,image/png,image/webp">
			<div class="hint">Listings with photos sell faster. First photo becomes the thumbnail.</div>
		</div>

		<div>
			<label for="cf-seller-name">Your name *</label>
			<input type="text" id="cf-seller-name" name="seller_name" required>
		</div>

		<div>
			<label for="cf-seller-email">Your email *</label>
			<input type="email" id="cf-seller-email" name="seller_email" required>
			<div class="hint">Used for the board to confirm your listing. Not shown publicly unless you toggle it on below.</div>
		</div>

		<div>
			<label for="cf-seller-phone">Your phone (optional)</label>
			<input type="tel" id="cf-seller-phone" name="seller_phone" placeholder="e.g. 206-555-0199">
		</div>

		<div class="full">
			<label>How should buyers reach you?</label>
			<div class="toggle-row">
				<label><input type="checkbox" name="show_email" value="1"> Show my email publicly</label>
				<label><input type="checkbox" name="show_phone" value="1"> Show my phone publicly</label>
			</div>
			<div class="hint">If you leave both unchecked, buyers email vmrainfo@gmail.com and we forward their message to you.</div>
		</div>

		<div class="full">
			<button class="cl-submit" type="submit" id="cf-submit">Post My Listing →</button>
		</div>

		<div class="cl-form-msg" id="cf-msg" role="status" aria-live="polite"></div>
	</form>
</div></section>

<section class="faq"><div class="faq-inner">
	<span class="eyebrow">§ Questions, Answered</span>
	<h2>Frequently Asked Questions</h2>
	<div class="faq-grid">
		<div class="faq-item"><h3>How much does it cost to list?</h3><p>Nothing. No listing fee, no commission when it sells, no signup. Fill out the form — the VMRA board reviews each submission and you get an email when yours is approved (usually within 24 hours).</p></div>
		<div class="faq-item"><h3>What can I sell?</h3><p>Anything that lives in a race shop: complete race cars, crate and built engines, takeoff Hoosiers, gauges, scales, tire racks, trailers, spares, body panels, parts. Vintage modified equipment is the focus; related stock-car gear is welcome.</p></div>
		<div class="faq-item"><h3>Who can buy or sell?</h3><p>VMRA members and friends of the Pacific Northwest vintage modified community. Membership is not required — if you're part of the broader PNW vintage modified circle, you can list and buy.</p></div>
		<div class="faq-item"><h3>What happens when it sells?</h3><p>Email <a href="mailto:vmrainfo@gmail.com?subject=SOLD">vmrainfo@gmail.com</a> with SOLD in the subject and we flip the badge on your listing. Sold listings stay visible so buyers can see recent comparables.</p></div>
		<div class="faq-item"><h3>Does VMRA handle payment or shipping?</h3><p>No. VMRA doesn't broker sales, handle payment, or arrange shipping. Buyer and seller deal directly — inspect items in person when possible.</p></div>
		<div class="faq-item"><h3>Why can't I include a link in my description?</h3><p>Links get stripped automatically to keep the board free of spam. If you have photos or docs to share, upload the photos to the listing directly or include them in your buyer's email reply.</p></div>
	</div>
</div></section>

</main>

<script>
(function(){
	/* ===== Filter + sort + search across server-rendered listings ===== */
	var grid = document.getElementById('listingsGrid');
	var searchBox = document.getElementById('searchBox');
	var sortSelect = document.getElementById('sortSelect');
	var chips = document.querySelectorAll('.chip');
	var resultCount = document.getElementById('resultCount');
	var activeFilter = 'all';

	function applyFilters(){
		var q = (searchBox.value || '').toLowerCase().trim();
		var cards = Array.from(grid.querySelectorAll('.listing'));
		var shown = 0;
		cards.forEach(function(c){
			var cat = c.getAttribute('data-category') || '';
			var title = c.getAttribute('data-title') || '';
			var desc = c.getAttribute('data-desc') || '';
			var matchesFilter = activeFilter === 'all' || cat === activeFilter;
			var matchesSearch = !q || title.indexOf(q) !== -1 || desc.indexOf(q) !== -1;
			var visible = matchesFilter && matchesSearch;
			c.style.display = visible ? '' : 'none';
			if (visible) shown++;
		});
		if (resultCount) resultCount.textContent = shown + (shown === 1 ? ' listing' : ' listings');
	}

	function applySort(){
		var mode = sortSelect.value;
		var cards = Array.from(grid.querySelectorAll('.listing'));
		cards.sort(function(a,b){
			if (mode === 'newest')    return (+b.dataset.posted) - (+a.dataset.posted);
			if (mode === 'oldest')    return (+a.dataset.posted) - (+b.dataset.posted);
			if (mode === 'price-low') return (+a.dataset.price || 0) - (+b.dataset.price || 0);
			if (mode === 'price-high')return (+b.dataset.price || 0) - (+a.dataset.price || 0);
			return 0;
		});
		cards.forEach(function(c){ grid.appendChild(c); });
	}

	chips.forEach(function(chip){
		chip.addEventListener('click', function(){
			chips.forEach(function(c){ c.setAttribute('aria-pressed','false'); });
			chip.setAttribute('aria-pressed','true');
			activeFilter = chip.getAttribute('data-filter');
			applyFilters();
		});
	});
	searchBox && searchBox.addEventListener('input', applyFilters);
	sortSelect && sortSelect.addEventListener('change', function(){ applySort(); applyFilters(); });
	applyFilters();
})();

(function(){
	/* ===== Submission form handler ===== */
	var form = document.getElementById('vmraClassifiedForm');
	if (!form) return;
	var submitBtn = document.getElementById('cf-submit');
	var msg = document.getElementById('cf-msg');
	var photoInput = document.getElementById('cf-photos');

	function showMsg(kind, text){
		msg.className = 'cl-form-msg ' + kind;
		msg.textContent = text;
		msg.scrollIntoView({ behavior: 'smooth', block: 'center' });
	}

	form.addEventListener('submit', function(ev){
		ev.preventDefault();
		msg.className = 'cl-form-msg';
		msg.textContent = '';

		// Client-side quick sanity: photo count + size
		if (photoInput && photoInput.files.length > 3) {
			return showMsg('err', 'Please select 3 photos or fewer.');
		}
		if (photoInput) {
			for (var i = 0; i < photoInput.files.length; i++) {
				if (photoInput.files[i].size > 5 * 1024 * 1024) {
					return showMsg('err', 'Each photo must be under 5 MB. "' + photoInput.files[i].name + '" is too large.');
				}
			}
		}

		var fd = new FormData(form);
		// Rename photo fields so REST endpoint picks them up as photo_0/1/2
		if (photoInput) {
			fd.delete('photos');
			for (var j = 0; j < Math.min(photoInput.files.length, 3); j++) {
				fd.append('photo_' + j, photoInput.files[j]);
			}
		}

		submitBtn.disabled = true;
		var prevLabel = submitBtn.textContent;
		submitBtn.textContent = 'Posting…';

		fetch('<?php echo esc_url( rest_url( 'vmra/v1/classified' ) ); ?>', {
			method: 'POST',
			body: fd,
			credentials: 'same-origin'
		}).then(function(r){
			return r.json().then(function(data){ return { ok: r.ok, status: r.status, data: data }; });
		}).then(function(res){
			if (res.ok && res.data && res.data.ok) {
				showMsg('ok', res.data.message || 'Submitted for board approval. You will get an email when it is live.');
				// Redirect to /classifieds/?submitted=1 — the pending post has no
				// public permalink yet, so land the user on the confirmation banner.
				var target = (res.data.redirect) || ('<?php echo esc_url( home_url( '/classifieds/?submitted=1' ) ); ?>');
				setTimeout(function(){ window.location.href = target; }, 1500);
			} else {
				var errText = (res.data && (res.data.message || res.data.code)) || ('Error ' + res.status);
				showMsg('err', errText);
				submitBtn.disabled = false;
				submitBtn.textContent = prevLabel;
			}
		}).catch(function(err){
			showMsg('err', 'Network error: ' + err.message + '. Please try again.');
			submitBtn.disabled = false;
			submitBtn.textContent = prevLabel;
		});
	});
})();
</script>

<?php
get_footer();
