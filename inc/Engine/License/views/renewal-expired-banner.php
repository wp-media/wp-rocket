<?php
/**
 * Renewal expired banner.
 *
 * @since 3.7.5
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="rocket-renewal-expired-banner" id="rocket-renewal-banner">
	<h3 class="rocket-expired-title"><?php esc_html_e( 'Your WP Rocket license is expired!', 'rocket' ); ?></h3>
	<div class="rocket-renewal-expired-banner-container">
		<div class="rocket-expired-message">
			<p>
			<?php
				printf(
					// translators: %1$s = <strong>, %2$s = </strong>.
					esc_html__( 'Your website could be much faster if it could take advantage of our %1$snew features and enhancements%2$s. 🚀', 'rocket' ),
					'<strong>',
					'</strong>'
				);
				?>
			</p>
			<p><?php echo $data['message']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		</div>
		<div class="rocket-expired-cta-container">
			<a href="<?php echo esc_url( $data['renewal_url'] ); ?>" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Renew now', 'rocket' ); ?></a>
		</div>
	</div>
	<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'rocket' ); ?></span></button>
</section>
