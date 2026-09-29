<?php
/**
 * Trial expired banner.
 *
 * @since 3.23.5
 */

defined( 'ABSPATH' ) || exit;

$data = isset( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<section class="rocket-renewal-expired-banner" id="rocket-renewal-banner">
	<div class="banner-copy">
		<h3 class="rocket-expired-title"><?php esc_html_e( 'Your WP Rocket trial has ended', 'rocket' ); ?></h3>
		<div class="rocket-renewal-expired-banner-container">
			<div class="rocket-expired-message">
				<p>
					<?php esc_html_e( 'Your site is no longer optimized, and key features have stopped working. You\'ve also lost plugin updates and support. Buy WP Rocket now to get it all back.', 'rocket' ); ?>
				</p>
			</div>
		</div>
	</div>
	<div class="rocket-expired-cta-container">
		<a href="<?php echo esc_url( $data['renewal_url'] ); ?>" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Get WP Rocket back', 'rocket' ); ?></a>
	</div>

	<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'rocket' ); ?></span></button>
</section>
