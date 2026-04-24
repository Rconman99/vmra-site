<?php
/**
 * Template for the /news/ page (News archive).
 * Ported from public/news/index.html.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$vmra_data_base = esc_url( VMRA_THEME_URI . '/data' );
get_header(); ?>

<style>
:root{
  --asphalt:#0e0e10;--asphalt-2:#17171a;--asphalt-3:#212126;--grease:#2a2a30;
  --chalk:#f4ede1;--chalk-dim:#c9c0ae;--race-red:#d11a2a;--sodium:#ffb319;
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

.news-list{display:grid;gap:22px}
.news-item{display:block;padding:28px 30px;background:var(--asphalt-2);border:1px solid var(--grease);text-decoration:none;color:var(--chalk);transition:border-color .2s,transform .2s}
.news-item:hover{border-color:var(--race-red);transform:translateY(-2px)}
.news-item .tag{font-family:'JetBrains Mono',monospace;font-size:.68rem;letter-spacing:.2em;text-transform:uppercase;color:var(--sodium);margin-bottom:10px;display:block}
.news-item.feature .tag{color:var(--race-red)}
.news-item h2{font-family:'Anton',sans-serif;font-size:1.6rem;line-height:1.1;letter-spacing:.02em;text-transform:uppercase;margin-bottom:10px}
.news-item p{color:var(--chalk-dim);font-size:1rem;line-height:1.55;margin-bottom:14px}
.news-meta{font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.12em;text-transform:uppercase;color:var(--chalk-dim);display:flex;justify-content:space-between;gap:14px;flex-wrap:wrap}
.news-meta .byline{color:var(--chalk)}
.news-meta .arr{color:var(--race-red)}

.news-updated{font-family:'JetBrains Mono',monospace;font-size:.7rem;letter-spacing:.12em;color:var(--chalk-dim);text-transform:uppercase;margin-top:24px;text-align:right}
</style>

<?php
$body = <<<'VMRA_BODY_EOT'
<section class="hero"><div class="hero-inner">
  <span class="eyebrow">§ 40th Anniversary · 2026 News Archive</span>
  <h1>From the Paddock.</h1>
  <p class="lede">Race recaps, previews, driver profiles, and club news from VMRA's 40th Anniversary season. Every green flag, every winner, every weekend.</p>
</div></section>

<main id="main-content" tabindex="-1">
  <div class="news-list" id="newsList">
    <a href="/news/class-of-2026" class="news-item feature">
      <span class="tag">§ Race Feature · Season Preview</span>
      <h2>Class of 2026: Champions Returning, Rookies Arriving — VMRA's 40th Season Roster</h2>
      <p>An inside look at the 23-driver grid lining up for VMRA's 40th anniversary season. The defending champ with a target on his back, the three-time champion looking for one more, the rookie class bringing new car numbers.</p>
      <div class="news-meta">
        <span>Apr 21, 2026 · by <span class="byline">The VMRA Desk</span></span>
        <span class="arr">Read Feature →</span>
      </div>
    </a>
  </div>
  <p class="news-updated" id="newsUpdated">Updated Apr 23, 2026</p>

  <script>
  (function(){
    fetch('/data/news.json')
      .then(function(r){ return r.json(); })
      .then(function(data){
        var items = (data.items || []).map(function(n){
          var feature = n.feature ? ' feature' : '';
          return '<a href="' + (n.link || '/news/class-of-2026') + '" class="news-item' + feature + '">' +
            '<span class="tag">' + (n.category || 'News') + '</span>' +
            '<h2>' + n.headline + '</h2>' +
            '<p>' + n.snippet + '</p>' +
            '<div class="news-meta">' +
              '<span>' + (n.date || '') + ' · by <span class="byline">' + (n.byline || '') + '</span></span>' +
              '<span class="arr">Read →</span>' +
            '</div>' +
          '</a>';
        }).join('');
        if (items) document.getElementById('newsList').innerHTML = items;
        if (data.updated) {
          var dt = new Date(data.updated + 'T12:00:00');
          var months = ["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"];
          document.getElementById('newsUpdated').textContent =
            'Updated ' + months[dt.getMonth()] + ' ' + dt.getDate() + ', ' + dt.getFullYear();
        }
      })
      .catch(function(){ /* keep pre-rendered fallback */ });
  })();
  </script>
</main>
VMRA_BODY_EOT;
$body = str_replace( "'/data/", "'" . $vmra_data_base . "/", $body );
$body = str_replace( '"/data/', '"' . $vmra_data_base . '/', $body );
echo $body;
?>

<?php get_footer();
