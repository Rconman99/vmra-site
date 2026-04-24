<?php
/**
 * Single Classified listing template.
 * URL: /classified/{slug}
 */
if ( ! defined( 'ABSPATH' ) ) { exit; }

get_header();
the_post();
$m = vmra_classified_meta();
$is_sold = ! empty( $m['sold_at'] );
$photos  = array_filter( array_merge(
	$m['primary_photo'] ? array( $m['primary_photo'] ) : array(),
	$m['extra_photos']
) );
?>
<style>
:root{--asphalt:#0e0e10;--asphalt-2:#17171a;--asphalt-3:#212126;--grease:#2a2a30;
  --chalk:#f4ede1;--chalk-dim:#c9c0ae;--race-red:#d11a2a;--sodium:#ffb319;--engine-blue:#2a5d8f;}
.cl-wrap{max-width:1100px;margin:0 auto;padding:40px 5vw}
.cl-crumbs{font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.15em;
  text-transform:uppercase;color:var(--chalk-dim);margin-bottom:18px}
.cl-crumbs a{color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor}
.cl-head{display:grid;grid-template-columns:1fr auto;gap:24px;align-items:end;
  border-bottom:1px solid var(--grease);padding-bottom:24px;margin-bottom:30px}
.cl-cat{font-family:'JetBrains Mono',monospace;font-size:.78rem;letter-spacing:.15em;
  text-transform:uppercase;color:var(--sodium);margin-bottom:8px}
.cl-title{font-family:'Anton',sans-serif;font-size:clamp(1.8rem,4.5vw,2.8rem);
  line-height:1.05;margin:0}
.cl-price{font-family:'Anton',sans-serif;font-size:2rem;color:var(--sodium);
  text-align:right;white-space:nowrap}
.cl-sold-banner{background:var(--race-red);color:var(--chalk);padding:10px 18px;
  font-family:'JetBrains Mono',monospace;font-size:.85rem;letter-spacing:.15em;
  text-transform:uppercase;text-align:center;margin-bottom:20px}
.cl-grid{display:grid;grid-template-columns:1.3fr 1fr;gap:40px}
@media (max-width:760px){.cl-grid{grid-template-columns:1fr}.cl-head{grid-template-columns:1fr}.cl-price{text-align:left}}
.cl-photos{display:flex;flex-direction:column;gap:12px}
.cl-photos img{width:100%;display:block;background:var(--asphalt-2);border:1px solid var(--grease)}
.cl-no-photo{aspect-ratio:4/3;background:var(--asphalt-2) repeating-linear-gradient(45deg,
  transparent 0,transparent 14px,rgba(255,255,255,.02) 14px,rgba(255,255,255,.02) 28px);
  display:flex;align-items:center;justify-content:center;color:var(--chalk-dim);
  font-family:'JetBrains Mono',monospace;font-size:.75rem;letter-spacing:.2em;
  text-transform:uppercase;border:1px solid var(--grease)}
.cl-body p{color:var(--chalk);line-height:1.65;margin:0 0 14px}
.cl-facts{background:var(--asphalt-2);border:1px solid var(--grease);padding:20px;margin-top:20px}
.cl-fact{display:grid;grid-template-columns:140px 1fr;padding:10px 0;border-bottom:1px solid var(--grease);font-size:.92rem}
.cl-fact:last-child{border-bottom:0}
.cl-fact .k{font-family:'JetBrains Mono',monospace;font-size:.72rem;letter-spacing:.15em;text-transform:uppercase;color:var(--chalk-dim)}
.cl-fact .v{color:var(--chalk)}
.cl-contact{background:var(--asphalt-3);border:2px solid var(--sodium);padding:22px;margin-top:30px}
.cl-contact h3{font-family:'Anton',sans-serif;font-size:1.3rem;margin:0 0 14px;color:var(--sodium)}
.cl-contact a{color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor;
  font-family:'JetBrains Mono',monospace}
.cl-contact .hint{color:var(--chalk-dim);font-size:.82rem;margin-top:10px}
.cl-back{display:inline-block;margin-top:40px;font-family:'JetBrains Mono',monospace;
  font-size:.82rem;letter-spacing:.12em;text-transform:uppercase;color:var(--sodium);
  text-decoration:none;border-bottom:1px solid currentColor}
</style>

<main id="main-content" class="cl-wrap">
	<nav class="cl-crumbs" aria-label="Breadcrumb">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a> · 
		<a href="<?php echo esc_url( home_url( '/classifieds/' ) ); ?>">Classifieds</a> · 
		<?php echo esc_html( $m['title'] ); ?>
	</nav>

	<?php if ( $is_sold ) : ?>
		<div class="cl-sold-banner" role="status">
			SOLD · <?php echo esc_html( $m['sold_at'] ); ?>
		</div>
	<?php endif; ?>

	<header class="cl-head">
		<div>
			<?php if ( $m['category'] ) : ?>
				<div class="cl-cat"><?php echo esc_html( ucfirst( $m['category'] ) ); ?></div>
			<?php endif; ?>
			<h1 class="cl-title"><?php echo esc_html( $m['title'] ); ?></h1>
		</div>
		<?php if ( $m['price'] ) : ?>
			<div class="cl-price"><?php echo esc_html( $m['price'] ); ?></div>
		<?php endif; ?>
	</header>

	<div class="cl-grid">
		<div class="cl-photos">
			<?php if ( ! empty( $photos ) ) : ?>
				<?php foreach ( $photos as $src ) : ?>
					<img src="<?php echo esc_url( $src ); ?>" alt="<?php echo esc_attr( $m['title'] ); ?>" loading="lazy">
				<?php endforeach; ?>
			<?php else : ?>
				<div class="cl-no-photo">No photo provided</div>
			<?php endif; ?>
		</div>

		<div>
			<div class="cl-body">
				<?php echo wp_kses_post( wpautop( wp_strip_all_tags( get_the_content() ) ) ); ?>
			</div>

			<div class="cl-facts">
				<?php if ( $m['location'] ) : ?>
					<div class="cl-fact"><span class="k">Location</span><span class="v"><?php echo esc_html( $m['location'] ); ?></span></div>
				<?php endif; ?>
				<?php if ( $m['condition'] ) : ?>
					<div class="cl-fact"><span class="k">Condition</span><span class="v"><?php echo esc_html( str_replace( '-', ' ', $m['condition'] ) ); ?></span></div>
				<?php endif; ?>
				<?php if ( $m['seller_name'] ) : ?>
					<div class="cl-fact"><span class="k">Seller</span><span class="v"><?php echo esc_html( $m['seller_name'] ); ?></span></div>
				<?php endif; ?>
				<div class="cl-fact"><span class="k">Posted</span><span class="v"><?php echo esc_html( $m['posted_human'] ); ?> ago</span></div>
			</div>

			<?php if ( $is_sold ) : ?>
				<div class="cl-contact">
					<h3>This item is sold.</h3>
					<p class="hint">Browsing for comparable recent sales? Head back to <a href="<?php echo esc_url( home_url( '/classifieds/' ) ); ?>">Classifieds</a>.</p>
				</div>
			<?php elseif ( $m['show_email'] || $m['show_phone'] ) : ?>
				<div class="cl-contact">
					<h3>Contact the Seller</h3>
					<?php if ( $m['show_email'] && $m['seller_email'] ) : ?>
						<p>Email: <a href="mailto:<?php echo esc_attr( $m['seller_email'] ); ?>?subject=<?php echo rawurlencode( 'VMRA Classified: ' . $m['title'] ); ?>"><?php echo esc_html( $m['seller_email'] ); ?></a></p>
					<?php endif; ?>
					<?php if ( $m['show_phone'] && $m['seller_phone'] ) : ?>
						<p>Phone / text: <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $m['seller_phone'] ) ); ?>"><?php echo esc_html( $m['seller_phone'] ); ?></a></p>
					<?php endif; ?>
					<p class="hint">VMRA doesn't broker the sale. Buyers and sellers deal directly. Inspect in person when possible.</p>
				</div>
			<?php else : ?>
				<div class="cl-contact">
					<h3>Contact the Seller</h3>
					<p>The seller chose to keep their info private. Email <a href="mailto:vmrainfo@gmail.com?subject=<?php echo rawurlencode( 'Classified inquiry: ' . $m['title'] ); ?>">vmrainfo@gmail.com</a> and we'll forward your message.</p>
					<p class="hint">Include your name, phone or email, and what you're asking about.</p>
				</div>
			<?php endif; ?>

			<a class="cl-back" href="<?php echo esc_url( home_url( '/classifieds/' ) ); ?>">← Back to all listings</a>
		</div>
	</div>
</main>

<?php
get_footer();
