<?php
/**
 * Renewal soon banner.
 *
 * @since 3.7.5
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="rocket-renewal-banner">
	<ul class="rocket-promo-countdown" id="rocket-renew-countdown">
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days"><?php echo esc_html( $data['countdown']['days'] ); ?></span> <?php esc_html_e( 'Days', 'rocket' ); ?></li>
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours"><?php echo esc_html( $data['countdown']['hours'] ); ?></span> <?php esc_html_e( 'Hours', 'rocket' ); ?></li>
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes"><?php echo esc_html( $data['countdown']['minutes'] ); ?></span> <?php esc_html_e( 'Minutes', 'rocket' ); ?></li>
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds"><?php echo esc_html( $data['countdown']['seconds'] ); ?></span> <?php esc_html_e( 'Seconds', 'rocket' ); ?></li>
	</ul>
	<div class="rocket-renew-message">
		<p>
			<?php
			printf(
				// translators: %1$s = <strong>, %2$s = </strong>.
				esc_html__( 'Your %1$sWP Rocket license is about to expire%2$s: you will soon lose access to product updates and support.', 'rocket' ),
				'<strong>',
				'</strong>'
			);
			?>
		</p>
		<p><?php echo $data['message']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
	</div>
	<div class="rocket-renew-cta-container">
		<a href="<?php echo esc_url( $data['renewal_url'] ); ?>" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Renew now', 'rocket' ); ?></a>
	</div>
</div>
