<?php
/**
 * Banned website banner.
 *
 * @since 3.20.4
 */

defined( 'ABSPATH' ) || exit;

$data = isset( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<section class="rocket-renewal-expired-banner revoked-website-banner" id="rocket-renewal-banner">
	<div class="banner-copy">
		<h3 class="rocket-expired-title"><?php esc_html_e( 'Your WP Rocket license has been revoked!', 'rocket' ); ?></h3>
		<div class="rocket-renewal-expired-banner-container">
			<div class="rocket-expired-message">
				<?php if ( ! empty( $data['message'] ) ) : ?>
				<p><?php echo $data['message']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="rocket-expired-cta-container">
		<a href="<?php echo esc_url( $data['purchase_url'] ); ?>" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'GET WP ROCKET AT 20% OFF', 'rocket' ); ?></a>
	</div>
</section>
