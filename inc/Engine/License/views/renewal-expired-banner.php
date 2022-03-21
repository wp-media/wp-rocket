<?php
/**
 * Renewal expired banner.
 *
 * @since 3.7.5
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="rocket-promo-banner" id="rocket-renewal-banner">
	<div class="rocket-expired-message">
		<h3 class="rocket-expired-title"><?php esc_html_e( 'Your WP Rocket license is expired!', 'rocket' ); ?></h3>
		<p>
		<?php
			printf(
				// translators: %1$s = <strong>, %2$s = </strong>.
				esc_html__( '%1$sYour website could be much faster%2$s if it could take advantage of our new features and enhancements. 🚀', 'rocket' ),
				'<strong>',
				'</strong>'
			);
			?>
		</p>
		<p>
		<?php
			printf(
				// translators: %1$s = <strong>, %2$s = </strong>.
				esc_html__( 'Renew your license to have access to the %1$slatest version of WP Rocket%2$s and to the wonderful %1$sassistance of our Support Team%2$s.', 'rocket' ),
				'<strong>',
				'</strong>'
			);
			?>
		</p>
	</div>
	<div class="rocket-expired-cta-container">
		<a href="<?php echo esc_url( $data['renewal_url'] ); ?>" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Renew now', 'rocket' ); ?></a>
	</div>
	<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'rocket' ); ?></span></button>
</div>
