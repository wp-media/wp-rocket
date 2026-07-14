<?php
/**
 * Reseller-banned licence notice partial.
 *
 * Renders a notice banner informing the user that RocketCDN has been disabled
 * because their (reseller) license was banned. Unlike the expired-license
 * notice, this partial has no renewal CTA — a reseller customer cannot
 * self-resolve a ban.
 *
 * ASSUMPTION: final copy has not been provided by design (the issue explicitly
 * flags "COPY IS NOT UPDATED IN THE VISUALIZATION"). The strings below are
 * reseller-neutral placeholders pending design/PM sign-off — see spec's Open
 * Questions section.
 *
 * @since 3.23
 */

?>

<div class="wpr-notice wpr-ri-notice wpr-cdn-expired__notice wpr-cdn-banned__notice" id="wpr-cdn-banned-banner">
	<div class="wpr-notice-container">
		<div class="wpr-notice-description wpr-notice-70">
			<h3 class="wpr-cdn-expired__notice-title">
				<?php esc_html_e( 'RocketCDN has been disabled', 'rocket' ); ?>
			</h3>
			<p><?php esc_html_e( 'RocketCDN has been disabled for this account. Please contact your license provider for assistance.', 'rocket' ); ?></p>
		</div>
	</div>
</div>
