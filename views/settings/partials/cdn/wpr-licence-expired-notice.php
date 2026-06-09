<?php
/**
 * Licence expired notice partial.
 *
 * Renders a notice banner prompting the user to renew their WP Rocket licence
 * to continue using RocketCDN.
 *
 * @param array $data {
 *     Notice data.
 *
 *     @type string $renewal_url URL to the licence renewal page.
 * }
 *
 * @since 3.22
 */

?>

<div class="wpr-notice wpr-ri-notice wpr-cdn-expired__notice" id="wpr-cdn-licence-banner">
	<div class="wpr-notice-container">
		<div class="wpr-notice-description wpr-notice-70">
			<h3 class="wpr-cdn-expired__notice-title">
				<?php esc_html_e( 'Your WPRocket license has expired', 'rocket' ); ?>
			</h3>
			<p><?php esc_html_e( 'Please renew it to keep using RocketCDN.', 'rocket' ); ?></p>
		</div>
		<a target="_blank" rel="noopener noreferrer" class="wpr-notice-close" href="<?php echo esc_url( $data['renewal_url'] ); ?>">
			<?php esc_html_e( 'Renew Licence', 'rocket' ); ?>
		</a>
	</div>
</div>