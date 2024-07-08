<?php
/**
 * Renewal expired banner with OCD disabled.
 *
 * @since 3.11.5
 */

defined( 'ABSPATH' ) || exit;

$data = isset( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<section class="rocket-renewal-expired-banner" id="rocket-renewal-banner">
	<h3 class="rocket-expired-title"><?php esc_html_e( 'The Optimize CSS Delivery feature is disabled.', 'rocket' ); ?></h3>
	<div class="rocket-renewal-expired-banner-container">
		<div class="rocket-expired-message">
			<p>
			<?php esc_html_e( 'You can no longer use the Remove Unused CSS or Load CSS asynchronously options.', 'rocket' ); ?>
				<br>
				<?php
				printf(
					// translators: %1$s = <strong>, %2$s = </strong>.
					esc_html__( 'You need an %1$sactive license%2$s to keep optimizing your CSS delivery, which addresses a PageSpeed Insights recommendation and improves your page performance.', 'rocket' ),
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
