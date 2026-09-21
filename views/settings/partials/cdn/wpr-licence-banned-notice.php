<?php
/**
 * Reseller-banned licence notice partial.
 *
 * Renders a notice banner informing the user that RocketCDN has been disabled
 * because their (reseller) license was banned. Unlike the expired-license
 * notice, this partial has no renewal CTA — a reseller customer cannot
 * self-resolve a ban.
 *
 * @since 3.23.1
 */

?>

<div class="wpr-rocketcdn-notice wpr-cdn-expired__notice wpr-cdn-banned__notice" id="wpr-cdn-banned-banner">
	<div class="wpr-notice-container">
		<div class="wpr-notice-70">
			<p><?php esc_html_e( 'Your WP Rocket license has been banned. Contact support for more information.', 'rocket' ); ?></p>
		</div>
	</div>
</div>
