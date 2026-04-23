<?php
/**
 * Site header — opens <html>, <head>, renders the anniversary banner,
 * canonical nav, and mobile-menu block. Mirrors the static site's
 * shell pattern exactly (same markup order: Banner → Nav → Mobile Menu).
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link rel="profile" href="https://gmpg.org/xfn/11">
	<link rel="icon" type="image/png" sizes="32x32" href="<?php echo esc_url( VMRA_THEME_URI . '/assets/media/favicon-32x32.png' ); ?>">
	<link rel="icon" type="image/png" sizes="16x16" href="<?php echo esc_url( VMRA_THEME_URI . '/assets/media/favicon-16x16.png' ); ?>">
	<link rel="apple-touch-icon" sizes="180x180" href="<?php echo esc_url( VMRA_THEME_URI . '/assets/media/apple-touch-icon.png' ); ?>">

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>

<?php wp_body_open(); ?>

<section class="anniversary-banner" aria-label="<?php esc_attr_e( '40th Anniversary 1986 to 2026', 'vmra' ); ?>">
	<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ab-link">
		<picture>
			<source type="image/avif"
			        srcset="<?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-480.avif' ); ?> 480w, <?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-800.avif' ); ?> 800w, <?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-1200.avif' ); ?> 1200w, <?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-1600.avif' ); ?> 1600w"
			        sizes="(max-width: 720px) 92vw, (max-width: 1200px) 84vw, 1100px">
			<source type="image/webp"
			        srcset="<?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-480.webp' ); ?> 480w, <?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-800.webp' ); ?> 800w, <?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-1200.webp' ); ?> 1200w, <?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-1600.webp' ); ?> 1600w"
			        sizes="(max-width: 720px) 92vw, (max-width: 1200px) 84vw, 1100px">
			<img src="<?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-1200.png' ); ?>"
			     srcset="<?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-480.png' ); ?> 480w, <?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-800.png' ); ?> 800w, <?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-1200.png' ); ?> 1200w, <?php echo esc_url( VMRA_THEME_URI . '/assets/media/anniversary-banner-1600.png' ); ?> 1600w"
			     sizes="(max-width: 720px) 92vw, (max-width: 1200px) 84vw, 1100px"
			     alt="<?php esc_attr_e( 'Northwest Vintage Modifieds — 40th Anniversary, 1986 to 2026', 'vmra' ); ?>"
			     width="1100" height="427"
			     loading="eager" fetchpriority="high" decoding="async">
		</picture>
	</a>
</section>

<nav class="main">
	<div class="nav-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-lockup" aria-label="<?php esc_attr_e( 'VMRA — home', 'vmra' ); ?>">
			<div class="logo-mark" aria-hidden="true">VM</div>
			<div class="logo-text">Vintage Modified<small>VMRA · NW · EST 1986</small></div>
		</a>

		<?php
		// Render the primary menu as <ul class="nav-links">. If the board hasn't
		// created a "Primary Nav" menu yet, fall back to a hardcoded list that
		// mirrors the static site so the theme works out of the box.
		if ( has_nav_menu( 'primary' ) ) :
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_class'     => 'nav-links',
				'container'      => false,
				'depth'          => 1,
				'fallback_cb'    => false,
			) );
		else :
		?>
			<ul class="nav-links">
				<li><a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'vmra' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/schedule/' ) ); ?>"><?php esc_html_e( 'Schedule', 'vmra' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/racers/' ) ); ?>"><?php esc_html_e( 'Drivers', 'vmra' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/standings/' ) ); ?>"><?php esc_html_e( 'Standings', 'vmra' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/rules/' ) ); ?>"><?php esc_html_e( 'Rules', 'vmra' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/classifieds/' ) ); ?>"><?php esc_html_e( 'Classifieds', 'vmra' ); ?></a></li>
				<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'vmra' ); ?></a></li>
			</ul>
		<?php endif; ?>

		<div class="nav-right">
			<a href="mailto:board@vmra.club?subject=Sponsorship%20Inquiry%20-%20VMRA%202026" class="nav-cta">
				<?php esc_html_e( 'Sponsor', 'vmra' ); ?> <span class="arr">›</span>
			</a>
			<button class="nav-toggle" id="navToggle"
			        aria-label="<?php esc_attr_e( 'Open menu', 'vmra' ); ?>"
			        aria-expanded="false" aria-controls="mobile-menu">
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>
</nav>

<div class="mobile-menu" id="mobile-menu" aria-hidden="true">
	<nav class="mobile-menu-inner" aria-label="<?php esc_attr_e( 'Mobile navigation', 'vmra' ); ?>">
		<?php
		// Same menu as the top nav, styled differently via .mm-link.
		if ( has_nav_menu( 'primary' ) ) :
			wp_nav_menu( array(
				'theme_location' => 'primary',
				'menu_class'     => 'mm-menu',
				'container'      => false,
				'depth'          => 1,
				'items_wrap'     => '<div class="mm-wrap">%3$s</div>',
				'link_class'     => 'mm-link',
				'fallback_cb'    => false,
			) );
		else :
		?>
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="mm-link"><?php esc_html_e( 'Home', 'vmra' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/schedule/' ) ); ?>" class="mm-link"><?php esc_html_e( 'Schedule', 'vmra' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/racers/' ) ); ?>" class="mm-link"><?php esc_html_e( 'Drivers', 'vmra' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/standings/' ) ); ?>" class="mm-link"><?php esc_html_e( 'Standings', 'vmra' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/rules/' ) ); ?>" class="mm-link"><?php esc_html_e( 'Rules', 'vmra' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/classifieds/' ) ); ?>" class="mm-link"><?php esc_html_e( 'Classifieds', 'vmra' ); ?></a>
			<a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>" class="mm-link"><?php esc_html_e( 'Contact', 'vmra' ); ?></a>
		<?php endif; ?>

		<div class="mm-divider"></div>
		<a href="mailto:board@vmra.club" class="mm-secondary">board@vmra.club</a>
		<a href="https://www.facebook.com/NWVMRA/" rel="me noopener" target="_blank" class="mm-secondary"><?php esc_html_e( 'VMRA on Facebook →', 'vmra' ); ?></a>
	</nav>
</div>
