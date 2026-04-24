<?php
/**
 * Site footer — canonical 4-column editorial footer.
 *
 * Boards can override the three link columns by creating WP menus for
 * "Footer · Racing", "Footer · Members", and "Footer · Connect".
 * If no menus exist, a hardcoded fallback renders the same links as
 * the static site.
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<footer class="shell">
	<div class="foot-inner">
		<div class="foot-brand">
			<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="logo-lockup" aria-label="<?php esc_attr_e( 'VMRA — home', 'vmra' ); ?>">
				<div class="logo-mark" aria-hidden="true">VM</div>
				<div class="logo-text">Vintage Modified<small>VMRA · NW · EST 1986</small></div>
			</a>
			<p><?php
				$brand_blurb = get_theme_mod(
					'vmra_footer_brand_blurb',
					__( "Northwest vintage modified stock car racing. Founded 1986 at Spanaway Speedway. Forty seasons of grassroots oval racing, still running on the same handshake rules.", 'vmra' )
				);
				echo esc_html( $brand_blurb );
			?></p>
		</div>

		<div>
			<h5><?php esc_html_e( 'Racing', 'vmra' ); ?></h5>
			<?php if ( has_nav_menu( 'footer-racing' ) ) : ?>
				<?php wp_nav_menu( array(
					'theme_location' => 'footer-racing',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => false,
				) ); ?>
			<?php else : ?>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/schedule/' ) ); ?>"><?php esc_html_e( 'Schedule', 'vmra' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/racers/' ) ); ?>"><?php esc_html_e( 'Drivers', 'vmra' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/standings/' ) ); ?>"><?php esc_html_e( 'Standings', 'vmra' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/tracks/' ) ); ?>"><?php esc_html_e( 'Tracks', 'vmra' ); ?></a></li>
				</ul>
			<?php endif; ?>
		</div>

		<div>
			<h5><?php esc_html_e( 'Members', 'vmra' ); ?></h5>
			<?php if ( has_nav_menu( 'footer-members' ) ) : ?>
				<?php wp_nav_menu( array(
					'theme_location' => 'footer-members',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => false,
				) ); ?>
			<?php else : ?>
				<ul>
					<li><a href="<?php echo esc_url( home_url( '/rules/' ) ); ?>"><?php esc_html_e( 'Rulebook', 'vmra' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/classifieds/' ) ); ?>"><?php esc_html_e( 'Classifieds', 'vmra' ); ?></a></li>
					<li><a href="<?php echo esc_url( VMRA_THEME_URI . '/assets/downloads/vmra-2026-membership-form.pdf' ); ?>" target="_blank" rel="noopener"><?php esc_html_e( 'Membership', 'vmra' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'Contact', 'vmra' ); ?></a></li>
				</ul>
			<?php endif; ?>
		</div>

		<div>
			<h5><?php esc_html_e( 'Connect', 'vmra' ); ?></h5>
			<?php if ( has_nav_menu( 'footer-connect' ) ) : ?>
				<?php wp_nav_menu( array(
					'theme_location' => 'footer-connect',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => false,
				) ); ?>
			<?php else : ?>
				<ul>
					<li><a href="mailto:vmrainfo@gmail.com">vmrainfo@gmail.com</a></li>
					<li><a href="https://www.facebook.com/NWVMRA/" rel="me noopener" target="_blank"><?php esc_html_e( 'Facebook', 'vmra' ); ?></a></li>
					<li><a href="<?php echo esc_url( home_url( '/news/' ) ); ?>"><?php esc_html_e( 'News', 'vmra' ); ?></a></li>
				</ul>
			<?php endif; ?>
		</div>
	</div>
	<div class="foot-bottom">
		<span><span class="chk"></span>© 1986 to <?php echo esc_html( date( 'Y' ) ); ?> · VMRA · <?php esc_html_e( 'All rights reserved', 'vmra' ); ?></span>
		<span><?php esc_html_e( '40th Anniversary Season · Made in the PNW', 'vmra' ); ?></span>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
