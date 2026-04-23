<?php
/**
 * Admin-side warnings about plugin dependencies.
 *
 * The theme renders correctly without ACF, but the custom fields (car
 * numbers, race rounds, winner relationships) need Advanced Custom Fields
 * to be edited via the WP admin. Without ACF, the board can only edit
 * titles + body copy — not enough to post a race update.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

add_action( 'admin_notices', function () {
	// Only show on the dashboard + theme-related screens.
	$screen = get_current_screen();
	if ( ! $screen || ! in_array( $screen->id, array( 'dashboard', 'themes', 'theme-editor' ), true ) ) {
		return;
	}

	// ACF is loaded when the plugin runs init. Its main class is ACF.
	if ( class_exists( 'ACF' ) ) {
		return;
	}
	?>
	<div class="notice notice-warning">
		<p>
			<strong>VMRA theme:</strong>
			Install the free <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=advanced+custom+fields&tab=search&type=term' ) ); ?>">Advanced Custom Fields</a> plugin to enable the driver, race, and news custom fields. The theme will display, but you can't post race updates until ACF is active.
		</p>
	</div>
	<?php
} );
