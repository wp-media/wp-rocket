<?php
/**
 * Renewal expired banner with OCD.
 *
 * @since 3.11.5
 */

defined( 'ABSPATH' ) || exit;

$data = isset( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<section class="rocket-renewal-expired-banner" id="rocket-renewal-banner">
	<div class="banner-copy">
		<h3 class="rocket-expired-title"><?php esc_html_e( 'Your license has expired. Keep your speed gains!', 'rocket' ); ?></h3>
		<div class="rocket-renewal-expired-banner-container">
			<div class="rocket-expired-message">
				<p>
					<?php
					printf(
						// translators: %1$s = <strong>, %2$s = </strong>, %3$s = date.
						esc_html__( "Your CSS optimization will stop working on %1\$s%3\$s%2\$s, and your pages will slow down, affecting your traffic and conversions. You'll also lose access to plugin updates and support.", 'rocket' ),
						'<strong>',
						'</strong>',
						esc_html( $data['disabled_date'] )
					);
					?>
				</p>
				<?php if ( ! empty( $data['message'] ) ) : ?>
				<p><?php echo $data['message']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
				<?php endif; ?>
			</div>
		</div>
	</div>
	<div class="rocket-expired-cta-container">
		<a href="<?php echo esc_url( $data['renewal_url'] ); ?>" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Renew Now', 'rocket' ); ?></a>
	</div>
	<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'rocket' ); ?></span></button>
</section>
