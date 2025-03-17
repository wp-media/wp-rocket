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
		<h3 class="rocket-expired-title"><?php esc_html_e( 'You will soon lose access to some features.', 'rocket' ); ?></h3>
		<div class="rocket-renewal-expired-banner-container">
			<div class="rocket-expired-message">
				<p>
					<?php
					printf(
						// translators: %1$s = <strong>, %2$s = </strong>.
						esc_html__( 'You need an %1$sactive license to continue optimizing your CSS delivery%2$s.', 'rocket' ),
						'<strong>',
						'</strong>'
					);
					?>
					<br>
					<?php esc_html_e( 'The Remove Unused CSS and Load CSS asynchronously features are great options to address the PageSpeed Insights recommendations and improve your website performance.', 'rocket' ); ?>
					<br>
					<?php
					printf(
						// translators: %1$s = <strong>, %2$s = </strong>, %3$s = date.
						esc_html__( 'These features will be %1$sautomatically disabled on %3$s%2$s.', 'rocket' ),
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
		<a href="<?php echo esc_url( $data['renewal_url'] ); ?>" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Renew now', 'rocket' ); ?></a>
	</div>
	<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'rocket' ); ?></span></button>
</section>
