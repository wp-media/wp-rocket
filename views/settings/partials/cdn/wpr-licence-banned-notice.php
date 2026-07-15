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

<div class="wpr-notice wpr-ri-notice wpr-cdn-expired__notice wpr-cdn-banned__notice" id="wpr-cdn-banned-banner">
	<div class="wpr-notice-container">
		<div class="wpr-notice-description wpr-notice-70">
			<h3 class="wpr-cdn-expired__notice-title">
				<?php esc_html_e( 'Your access to RocketCDN has been paused.', 'rocket' ); ?>
			</h3>
			<p><?php esc_html_e( 'Your hosting provider has paused this license. Please contact their support team for assistance.', 'rocket' ); ?></p>
		</div>
	</div>
</div>
