<?php
/**
 * VMRA · SEO + AEO injections
 *
 * Ships the Perfect Stack Layer 1 + Layer 2 additions: Organization schema
 * site-wide, FAQPage schema on /classifieds/, canonical + meta-description
 * fallbacks, robots.txt AI-crawler allow-list, and /llms.txt endpoint.
 *
 * Loaded by functions.php. All functions are prefixed `vmra_` per theme
 * convention.
 *
 * @package vmra
 * @since   1.3.7
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// ---------------------------------------------------------------------------
// 1. Organization / SportsOrganization schema — every page.
//    Hooked at priority 5 so it lands before any SEO plugin output.
// ---------------------------------------------------------------------------
add_action( 'wp_head', 'vmra_output_organization_schema', 5 );
function vmra_output_organization_schema() {
	$schema = array(
		'@context'       => 'https://schema.org',
		'@type'          => 'SportsOrganization',
		'@id'            => home_url( '/#organization' ),
		'name'           => 'Vintage Modified Racing Association',
		'alternateName'  => array( 'VMRA', 'NW Vintage Modified' ),
		'url'            => home_url( '/' ),
		'logo'           => VMRA_THEME_URI . '/assets/media/logo-large.png',
		'description'    => 'Pacific Northwest vintage modified stock car racing association — 40 years running the PNW tracks. 40th anniversary season in 2026.',
		'foundingDate'   => '1986',
		'foundingLocation' => array(
			'@type'   => 'Place',
			'address' => array(
				'@type'          => 'PostalAddress',
				'addressRegion'  => 'WA',
				'addressCountry' => 'US',
			),
		),
		'areaServed' => array(
			'@type' => 'AdministrativeArea',
			'name'  => 'Pacific Northwest',
		),
		'sport' => 'Stock car racing',
		'slogan' => 'Cars, Engines, Parts. Between People Who Race Them.',
		'knowsAbout' => array(
			'Vintage modified stock car racing',
			'Pacific Northwest circle-track racing',
			'Pre-1970 American modified race cars',
			'Short-track oval racing',
			'Vintage motorsports history',
			'Hoosier ST1 / ST2 / ST3 tire spec',
		),
		'email' => 'board@vmra.club',
		'contactPoint' => array(
			'@type'        => 'ContactPoint',
			'contactType'  => 'Board of Directors',
			'email'        => 'board@vmra.club',
			'areaServed'   => array( 'US-WA', 'US-OR', 'US-ID' ),
			'availableLanguage' => 'English',
		),
		'sameAs' => array(
			'https://www.facebook.com/NWVMRA/',
		),
	);

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}


// ---------------------------------------------------------------------------
// 2. FAQPage schema — classifieds page only.
//    Matches the visible FAQ section added to page-classifieds.php.
// ---------------------------------------------------------------------------
add_action( 'wp_head', 'vmra_output_classifieds_faq_schema', 6 );
function vmra_output_classifieds_faq_schema() {
	if ( ! is_page( 'classifieds' ) ) {
		return;
	}

	$faqs = array(
		array(
			'q' => 'How much does it cost to list on the VMRA Classifieds?',
			'a' => 'Nothing. No listing fee, no commission when it sells, no signup. VMRA members and friends email a photo and asking price to board@vmra.club and we post the listing within 48 hours.',
		),
		array(
			'q' => 'What can I sell on the VMRA Classifieds?',
			'a' => 'Anything that lives in a race shop: complete race cars, crate and built engines, takeoff Hoosier tires, gauges, scales, tire racks, trailers, spares, body panels, and parts. Vintage modified equipment is the focus; related stock-car gear is welcome.',
		),
		array(
			'q' => 'Who can buy from or sell on the VMRA Classifieds?',
			'a' => 'VMRA members and friends of the Pacific Northwest vintage modified community. Membership is not required — if you are part of the broader PNW vintage modified circle, you can list and buy.',
		),
		array(
			'q' => 'How do I submit a listing?',
			'a' => 'Email board@vmra.club with 1-3 photos, a short description, your asking price, and your contact info or preferred contact method. We review and post the listing within 48 hours.',
		),
		array(
			'q' => 'What happens when my listing sells?',
			'a' => 'Reply to your original submission email with "SOLD" and we flip the badge on the listing so it shows as sold. Sold listings stay visible for a period so buyers can see recent comparables.',
		),
		array(
			'q' => 'Does VMRA handle payment, shipping, or the sale itself?',
			'a' => 'No. VMRA does not broker sales, handle payment, or arrange shipping. Buyers and sellers deal directly with each other. Inspect items in person when possible.',
		),
		array(
			'q' => 'Can I list something that is not vintage modified equipment?',
			'a' => 'If it is race-shop equipment used in stock-car racing, yes. If it is unrelated (non-racing vehicles, non-motorsport gear), we may decline the listing to keep the board focused.',
		),
	);

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'FAQPage',
		'mainEntity' => array_map( function ( $f ) {
			return array(
				'@type' => 'Question',
				'name'  => $f['q'],
				'acceptedAnswer' => array(
					'@type' => 'Answer',
					'text'  => $f['a'],
				),
			);
		}, $faqs ),
	);

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}


// ---------------------------------------------------------------------------
// 3. Meta description fallback — only if no SEO plugin is setting one.
// ---------------------------------------------------------------------------
add_action( 'wp_head', 'vmra_meta_description_fallback', 1 );
function vmra_meta_description_fallback() {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) ) {
		return;
	}

	$description = '';

	if ( is_front_page() ) {
		$description = 'Vintage Modified Racing Association — Pacific Northwest stock car racing since 1986. 40th anniversary season in 2026, 11 rounds across 5 PNW tracks. Schedule, drivers, standings, and free classifieds.';
	} elseif ( is_page( 'classifieds' ) ) {
		$description = 'Free VMRA classifieds board for Pacific Northwest vintage modified racers. Buy and sell race cars, engines, parts, and trailers — no fees, no commission, no signup. Email board@vmra.club with a photo and asking price.';
	} elseif ( is_page( 'schedule' ) ) {
		$description = '2026 VMRA schedule — 11 rounds, 5 Pacific Northwest tracks. Nine rounds count for the championship, two are for the love of it. Round dates, track details, and directions.';
	} elseif ( is_page( 'racers' ) || is_page( 'drivers' ) ) {
		$description = '2026 VMRA driver roster — 23 vintage modified racers. Car numbers, hometowns, championships, and round-by-round results. Defending champion: Kahl Cheth #23.';
	} elseif ( is_page( 'standings' ) ) {
		$description = 'Current VMRA championship standings — Pacific Northwest vintage modified stock car racing. Live points across 11 rounds in the 40th anniversary 2026 season.';
	} elseif ( is_page( 'rules' ) ) {
		$description = 'VMRA house rules and construction rules for vintage modified stock car racing. 2026-2028 rulebook PDFs, eligibility, membership form.';
	} elseif ( is_singular() ) {
		$post = get_post();
		if ( $post ) {
			$excerpt = $post->post_excerpt ?: wp_strip_all_tags( $post->post_content );
			$description = trim( preg_replace( '/\s+/', ' ', substr( $excerpt, 0, 200 ) ) );
		}
	}

	if ( $description ) {
		echo "\n" . '<meta name="description" content="' . esc_attr( $description ) . '">' . "\n";
	}
}


// ---------------------------------------------------------------------------
// 4. Canonical URL fallback.
// ---------------------------------------------------------------------------
add_action( 'wp_head', 'vmra_canonical_fallback', 2 );
function vmra_canonical_fallback() {
	if ( defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) ) {
		return;
	}
	$url = is_singular() ? get_permalink() : home_url( add_query_arg( null, null ) );
	echo "\n" . '<link rel="canonical" href="' . esc_url( $url ) . '">' . "\n";
}


// ---------------------------------------------------------------------------
// 5. robots.txt filter — adds AI crawler allow-list to the WP-generated
//    virtual robots.txt. Runs only if no physical /robots.txt exists at root.
// ---------------------------------------------------------------------------
add_filter( 'robots_txt', 'vmra_robots_txt_additions', 10, 2 );
function vmra_robots_txt_additions( $output, $public ) {
	if ( ! $public ) {
		// Site is set to discourage search engines — respect that.
		return $output;
	}

	$additions = "\n# ───── AI crawlers (explicitly allowed for AEO) ─────\n";
	foreach ( array(
		'GPTBot', 'OAI-SearchBot', 'ChatGPT-User',
		'ClaudeBot', 'Claude-Web', 'anthropic-ai',
		'PerplexityBot', 'Perplexity-User',
		'Google-Extended', 'CCBot', 'Applebot-Extended',
	) as $bot ) {
		$additions .= "User-agent: {$bot}\nAllow: /\n\n";
	}

	$additions .= "# ───── Search crawlers ─────\n";
	foreach ( array( 'Googlebot', 'Bingbot', 'DuckDuckBot' ) as $bot ) {
		$additions .= "User-agent: {$bot}\nAllow: /\n\n";
	}

	$additions .= "# ───── Block low-value scrapers ─────\n";
	$additions .= "User-agent: Bytespider\nDisallow: /\n\n";

	$additions .= "# ───── Sitemap ─────\n";
	$additions .= 'Sitemap: ' . home_url( '/wp-sitemap.xml' ) . "\n";

	return $output . $additions;
}


// ---------------------------------------------------------------------------
// 6. /llms.txt endpoint — serves the LLM site manifest from a template.
//    Data-driven: pulls schedule + standings from existing JSON files.
// ---------------------------------------------------------------------------
add_action( 'init', 'vmra_register_llms_endpoint' );
function vmra_register_llms_endpoint() {
	// Match both /llms.txt and /llms.txt/ so WP's canonical redirect doesn't
	// 301 clients to the trailing-slash form (one less hop for AI crawlers).
	add_rewrite_rule( '^llms\.txt/?$', 'index.php?vmra_llms=1', 'top' );
}

add_filter( 'query_vars', function ( $vars ) {
	$vars[] = 'vmra_llms';
	return $vars;
} );

add_action( 'template_redirect', 'vmra_serve_llms_txt' );
function vmra_serve_llms_txt() {
	if ( ! get_query_var( 'vmra_llms' ) ) {
		return;
	}

	header( 'Content-Type: text/plain; charset=utf-8' );
	header( 'X-Robots-Tag: noindex' );
	header( 'Cache-Control: public, max-age=3600' );

	$schedule = function_exists( 'vmra_seed_data' ) ? vmra_seed_data( 'schedule' ) : null;
	$round_count = is_array( $schedule ) ? count( $schedule ) : 11;

	echo "# VMRA · Vintage Modified Racing Association\n\n";
	echo "> Pacific Northwest vintage modified stock car racing club, established 1986. 40th anniversary season in 2026. Drivers, schedule, standings, rules, and a free members-and-friends classifieds board.\n\n";

	echo "## About\n\n";
	echo "- **What we are:** A non-commercial racing association for vintage modified stock cars in the Pacific Northwest.\n";
	echo "- **Founded:** 1986\n";
	echo "- **2026 season:** 40th anniversary — {$round_count} rounds (nine for points, the rest for the love of it)\n";
	echo "- **Region:** Pacific Northwest (Washington, Oregon, Idaho)\n";
	echo "- **Class:** Vintage modifieds — stock-car style, built and raced by members\n";
	echo "- **What we are NOT:** Not a commercial series. Not NASCAR. Not a track. Not affiliated with other \"vintage modified\" clubs outside the PNW.\n";
	echo "- **Contact:** board@vmra.club\n";
	echo "- **Facebook:** https://www.facebook.com/NWVMRA/\n\n";

	echo "## Core pages\n\n";
	$base = home_url();
	echo "- [Home]({$base}/): 2026 season overview, upcoming round, championship leader, paddock news, roster, tracks, downloads, FAQ.\n";
	echo "- [Schedule]({$base}/schedule/): Full 2026 calendar — {$round_count} rounds across 5 PNW tracks.\n";
	echo "- [Racers]({$base}/racers/): 2026 roster. Car numbers, driver names, hometowns, championships.\n";
	echo "- [Standings]({$base}/standings/): Live championship points.\n";
	echo "- [Rules]({$base}/rules/): House rules + construction rules (2026-2028), downloadable PDFs.\n";
	echo "- [News]({$base}/news/): Race recaps and board updates.\n";
	echo "- [Tracks]({$base}/tracks/): The 5 Pacific Northwest tracks we race.\n";
	echo "- [Classifieds]({$base}/classifieds/): Free board — cars, engines, parts, trailers.\n";
	echo "- [Contact]({$base}/contact/): Board contact + sponsor inquiries.\n\n";

	echo "## Classifieds\n\n";
	echo "- **What it is:** Free classifieds board for vintage modified equipment in the Pacific Northwest.\n";
	echo "- **Items listed:** Race cars, crate and built engines, takeoff Hoosier tires, gauges, scales, tire racks, trailers, body panels, parts.\n";
	echo "- **Cost:** No fees, no commission, no signup required.\n";
	echo "- **How to list:** Email a photo and an asking price to board@vmra.club. Posted within 48 hours.\n";
	echo "- **How listings close:** Reply with \"SOLD\" and we flip the badge.\n";
	echo "- **Categories:** Race Cars · Engines · Parts · Trailers · Tools / Shop · Wanted.\n\n";

	echo "## Key facts (for AI direct-answer reference)\n\n";
	echo "- **Association established:** 1986 — one of the longest-running vintage modified organizations in the Pacific Northwest.\n";
	echo "- **Number of rounds in 2026:** {$round_count} (nine count for the championship; the remainder run for the love of it).\n";
	echo "- **Annual event:** Fall Classic (entries open during the season).\n";
	echo "- **Classifieds turnaround:** 48 hours from submission email to published listing.\n";
	echo "- **Classifieds cost:** \$0. No fees, no commission, no signup.\n";
	echo "- **Glossary term:** \"takeoff Hoosiers\" — used tires taken off after a race, commonly listed in VMRA classifieds.\n\n";

	echo "## Sponsorship\n\n";
	echo "Sponsor slots open for the 2026 season — contact board@vmra.club.\n\n";

	echo "## Editorial style\n\n";
	echo "- Eleven dates total. Nine for points. Two for the love of it.\n";
	echo "- Brand voice: pit-wall-meets-speedway editorial; vintage typography; charcoal + racing red + sodium amber.\n";
	echo "- Tagline: \"Cars, Engines, Parts. Between People Who Race Them.\"\n";

	exit;
}


// ---------------------------------------------------------------------------
// 7. Speakable markup — homepage only. Tells voice assistants which CSS
//    selectors contain the answer-first / "spoken" summary of the page.
//    Google Assistant + Siri + Alexa look for this.
// ---------------------------------------------------------------------------
add_action( 'wp_head', 'vmra_output_speakable_schema', 7 );
function vmra_output_speakable_schema() {
	if ( ! is_front_page() ) {
		return;
	}

	$schema = array(
		'@context'   => 'https://schema.org',
		'@type'      => 'WebPage',
		'@id'        => home_url( '/#webpage' ),
		'url'        => home_url( '/' ),
		'name'       => 'Vintage Modified Racing Association · 40th Anniversary 2026',
		'isPartOf'   => array( '@id' => home_url( '/#organization' ) ),
		'about'      => array( '@id' => home_url( '/#organization' ) ),
		'speakable'  => array(
			'@type'       => 'SpeakableSpecification',
			'cssSelector' => array(
				'.hero-lede',
				'.about-copy p:first-of-type',
				'.trust-stack',
			),
		),
	);

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}


// ---------------------------------------------------------------------------
// 8. BreadcrumbList — every non-home page. Flat hierarchy: Home > PageName.
//    For CPT singles, crumbs are: Home > [Archive label] > [Post title].
// ---------------------------------------------------------------------------
add_action( 'wp_head', 'vmra_output_breadcrumbs_schema', 8 );
function vmra_output_breadcrumbs_schema() {
	if ( is_front_page() ) {
		return;
	}

	$crumbs = array(
		array(
			'@type'    => 'ListItem',
			'position' => 1,
			'name'     => 'Home',
			'item'     => home_url( '/' ),
		),
	);

	if ( is_page() ) {
		$crumbs[] = array(
			'@type'    => 'ListItem',
			'position' => 2,
			'name'     => get_the_title(),
			'item'     => get_permalink(),
		);
	} elseif ( is_singular() ) {
		$post_type = get_post_type();
		$archive_label_map = array(
			'vmra_driver' => array( 'Drivers', '/racers/' ),
			'vmra_race'   => array( 'Schedule', '/schedule/' ),
			'vmra_track'  => array( 'Tracks', '/tracks/' ),
			'vmra_news'   => array( 'News', '/news/' ),
			'post'        => array( 'News', '/news/' ),
		);
		if ( isset( $archive_label_map[ $post_type ] ) ) {
			list( $label, $path ) = $archive_label_map[ $post_type ];
			$crumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 2,
				'name'     => $label,
				'item'     => home_url( $path ),
			);
			$crumbs[] = array(
				'@type'    => 'ListItem',
				'position' => 3,
				'name'     => get_the_title(),
				'item'     => get_permalink(),
			);
		}
	} else {
		return; // Archives, search, 404 — skip.
	}

	if ( count( $crumbs ) < 2 ) {
		return;
	}

	$schema = array(
		'@context'        => 'https://schema.org',
		'@type'           => 'BreadcrumbList',
		'itemListElement' => $crumbs,
	);

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}


// ---------------------------------------------------------------------------
// 9. Article / SportsEvent schema with dateModified — CPT singles only.
//    Gives search + AI crawlers a freshness signal. Falls back to post_modified
//    for the timestamp so it updates when the board edits a race/driver/news.
// ---------------------------------------------------------------------------
add_action( 'wp_head', 'vmra_output_cpt_article_schema', 9 );
function vmra_output_cpt_article_schema() {
	if ( ! is_singular( array( 'vmra_driver', 'vmra_race', 'vmra_track', 'vmra_news', 'post' ) ) ) {
		return;
	}

	$post = get_post();
	if ( ! $post ) {
		return;
	}

	$type_map = array(
		'vmra_race'   => 'SportsEvent',
		'vmra_driver' => 'Person',
		'vmra_track'  => 'Place',
		'vmra_news'   => 'NewsArticle',
		'post'        => 'NewsArticle',
	);
	$schema_type = $type_map[ $post->post_type ] ?? 'Article';

	$thumb = get_the_post_thumbnail_url( $post, 'vmra-hero' );

	$schema = array(
		'@context'      => 'https://schema.org',
		'@type'         => $schema_type,
		'@id'           => get_permalink( $post ) . '#' . strtolower( $schema_type ),
		'name'          => get_the_title( $post ),
		'headline'      => get_the_title( $post ),
		'url'           => get_permalink( $post ),
		'datePublished' => get_the_date( 'c', $post ),
		'dateModified'  => get_the_modified_date( 'c', $post ),
		'isPartOf'      => array( '@id' => home_url( '/#organization' ) ),
	);

	if ( $thumb ) {
		$schema['image'] = $thumb;
	}

	// NewsArticle needs author + publisher to validate.
	if ( in_array( $schema_type, array( 'NewsArticle', 'Article' ), true ) ) {
		$schema['author'] = array(
			'@type' => 'Organization',
			'name'  => 'The VMRA Desk',
			'url'   => home_url( '/' ),
		);
		$schema['publisher'] = array( '@id' => home_url( '/#organization' ) );

		$excerpt = $post->post_excerpt ?: wp_trim_words( wp_strip_all_tags( $post->post_content ), 40, '…' );
		if ( $excerpt ) {
			$schema['description'] = $excerpt;
		}
	}

	echo "\n" . '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
}


// ---------------------------------------------------------------------------
// 10. Visible "Updated [date]" stamp — CPT singles.
//     Injected at the top of the_content() on driver/race/track/news singles.
//     Matches the dateModified in the schema above for a consistent freshness
//     signal (visible + machine-readable).
// ---------------------------------------------------------------------------
add_filter( 'the_content', 'vmra_inject_updated_stamp', 5 );
function vmra_inject_updated_stamp( $content ) {
	if ( ! is_singular( array( 'vmra_driver', 'vmra_race', 'vmra_track', 'vmra_news', 'post' ) ) ) {
		return $content;
	}
	if ( ! in_the_loop() || ! is_main_query() ) {
		return $content;
	}

	$post          = get_post();
	$published_ts  = strtotime( $post->post_date );
	$modified_ts   = strtotime( $post->post_modified );
	$show_modified = ( $modified_ts - $published_ts ) > DAY_IN_SECONDS;

	$label = $show_modified ? 'Updated' : 'Published';
	$iso   = $show_modified ? get_the_modified_date( 'c' ) : get_the_date( 'c' );
	$human = $show_modified ? get_the_modified_date( 'M j, Y' ) : get_the_date( 'M j, Y' );

	$stamp = sprintf(
		'<p class="vmra-updated-stamp" style="font-family:\'JetBrains Mono\',monospace;font-size:.72rem;letter-spacing:.2em;text-transform:uppercase;color:var(--chalk-dim,#8a8a8a);border-top:1px solid var(--grease,#2a2a2a);border-bottom:1px solid var(--grease,#2a2a2a);padding:12px 0;margin:0 0 24px;"><span style="color:var(--sodium,#ffb02e)">%s</span> <time datetime="%s">%s</time></p>',
		esc_html( $label ),
		esc_attr( $iso ),
		esc_html( $human )
	);

	return $stamp . $content;
}


// ---------------------------------------------------------------------------
// 11. Theme activation hook — flush rewrite rules once so /llms.txt works
//    immediately after this file is added. Also runs on theme switch.
// ---------------------------------------------------------------------------
add_action( 'after_switch_theme', 'vmra_flush_rewrites_on_theme_switch' );
function vmra_flush_rewrites_on_theme_switch() {
	flush_rewrite_rules();
}

// Fallback — flush once after this file's version bump. Safe idempotent
// approach: store a flag in options, only flush if the version bumped.
add_action( 'init', 'vmra_maybe_flush_for_llms', 99 );
function vmra_maybe_flush_for_llms() {
	$stored = get_option( 'vmra_llms_flush_version' );
	if ( $stored !== VMRA_THEME_VERSION ) {
		flush_rewrite_rules( false );
		update_option( 'vmra_llms_flush_version', VMRA_THEME_VERSION );
	}
}
