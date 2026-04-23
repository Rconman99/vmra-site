<?php
/**
 * Template for the /contact/ page.
 * Ported from the static public/contact/index.html.
 *
 * WP auto-loads this template when a Page with slug "contact" is viewed.
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

.contact-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px;margin-bottom:60px}
.contact-card{background:var(--asphalt-2);border:1px solid var(--grease);padding:32px;transition:border-color .2s}
.contact-card:hover{border-color:var(--race-red)}
.contact-tag{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-size:.7rem;letter-spacing:.18em;text-transform:uppercase;margin-bottom:12px}
.contact-title{font-family:'Anton',sans-serif;font-size:1.5rem;line-height:1.1;margin-bottom:10px}
.contact-desc{color:var(--chalk-dim);margin-bottom:18px;font-size:.95rem}
.contact-link{display:inline-block;font-family:'JetBrains Mono',monospace;font-size:.85rem;letter-spacing:.08em;color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor;padding-bottom:2px}
.contact-link:hover{color:var(--race-red)}

section{margin-bottom:50px}
section .marker{font-family:'JetBrains Mono',monospace;color:var(--sodium);font-size:.78rem;letter-spacing:.2em;text-transform:uppercase;margin-bottom:12px;display:block}
h2{font-family:'Anton',sans-serif;font-size:clamp(1.8rem,4vw,2.6rem);line-height:1.1;margin-bottom:18px;letter-spacing:.02em}
p{margin-bottom:14px;color:var(--chalk-dim)}
p strong{color:var(--chalk)}

.classifieds{background:var(--asphalt-2);border:1px solid var(--grease);padding:32px;border-left:3px solid var(--race-red)}
</style>

<?php
$body = <<<'VMRA_BODY_EOT'
<section class="hero"><div class="hero-inner">
  <span class="eyebrow">§ Contact · Get in Touch</span>
  <h1>Talk to the Board.</h1>
  <p class="lede">Tech question, want to bring a car out, looking to sponsor the 40th season — pick the channel that fits below. We try to answer emails within a couple days, but if it's a race weekend we're at the track. Worst case, message us on Facebook and we'll catch it in the trailer.</p>
</div></section>

<main>

  <div class="contact-grid">
    <div class="contact-card" style="border-color:var(--race-red)">
      <div class="contact-tag" style="color:var(--race-red)">Join VMRA · 2026</div>
      <div class="contact-title">Membership Application</div>
      <p class="contact-desc">$50 annual dues. Print, fill out, sign, and bring to the next race weekend with cash or check made out to VMRA. Required to score points.</p>
      <a class="contact-link" href="/downloads/vmra-2026-membership-form.pdf" target="_blank" rel="noopener">↓ Download 2026 Form (PDF)</a>
    </div>

    <div class="contact-card">
      <div class="contact-tag">General · Board</div>
      <div class="contact-title">Email the Board</div>
      <p class="contact-desc">Tech questions, race-day logistics, anything else.</p>
      <a class="contact-link" href="mailto:board@vmra.club">board@vmra.club →</a>
    </div>

    <div class="contact-card">
      <div class="contact-tag">Sponsorship</div>
      <div class="contact-title">Put Your Logo on the Half-Mile</div>
      <p class="contact-desc">Title-sponsor the season, back a single race, or get on a car's hood. Tiered packages for businesses of any size.</p>
      <a class="contact-link" href="mailto:board@vmra.club?subject=Sponsorship%20Inquiry%20-%20VMRA%202026">board@vmra.club →</a>
    </div>

    <div class="contact-card">
      <div class="contact-tag">Apple Cup · Pre-Reg</div>
      <div class="contact-title">Tri-City Apple Cup</div>
      <p class="contact-desc">Online pre-registration is handled through Tri-City Raceway directly.</p>
      <a class="contact-link" href="https://tricityraceway.com/drivers.html" target="_blank" rel="noopener">tricityraceway.com →</a>
    </div>

    <div class="contact-card">
      <div class="contact-tag">Social</div>
      <div class="contact-title">Follow on Facebook</div>
      <p class="contact-desc">Race-night updates, photos, points changes, and shop talk happen on the VMRA Facebook page.</p>
      <a class="contact-link" href="https://www.facebook.com/NWVMRA/" rel="me noopener" target="_blank">facebook.com/NWVMRA →</a>
    </div>
  </div>

  <section>
    <span class="marker">§ Classifieds</span>
    <h2>Cars &amp; Parts for Sale.</h2>
    <div class="classifieds">
      <p><strong>Free for members and supporters.</strong> Selling a vintage modified, a spare engine, takeoff tires, or trailer space? Email the webmaster with photos, asking price, and your best contact number — we'll post it on this page.</p>
      <p>No fees, no commission, no signup. Just real cars and parts moving between people who actually race them.</p>
      <a class="contact-link" href="/classifieds/" style="margin-right:18px">Browse Classifieds →</a><a class="contact-link" href="mailto:board@vmra.club?subject=Classified%20Listing%20Submission">Submit a listing →</a>
    </div>
  </section>

  <section>
    <span class="marker">§ About VMRA</span>
    <h2>Founded 1986. Nonprofit.</h2>
    <p>The Vintage Modified Racing Association is a Pacific Northwest 501(c)(7) nonprofit racing club. We organize circle-track races for vintage modified stock cars across Washington, Oregon, and Idaho. Founding location: Spanaway Speedway, 1986. The 40th anniversary season runs in 2026.</p>
    <p>Membership: <strong>$50 per year</strong>. <a href="/downloads/vmra-2026-membership-form.pdf" target="_blank" rel="noopener" style="color:var(--sodium);text-decoration:none;border-bottom:1px solid currentColor">Download the 2026 Membership Application (PDF)</a>, print, sign, bring to the next race with cash or check made out to VMRA. The board operates with a $200 spending cap before requiring a full-membership vote — every dollar gets accounted for.</p>
  </section>

</main>
VMRA_BODY_EOT;

// Retarget /data/*.json fetches at the theme's data dir.
$body = str_replace( "'/data/", "'" . $vmra_data_base . "/", $body );
$body = str_replace( '"/data/', '"' . $vmra_data_base . '/', $body );
echo $body;
?>

<?php get_footer();
