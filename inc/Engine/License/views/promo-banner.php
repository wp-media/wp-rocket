<?php
/**
 * Promo banner.
 *
 * @since 3.7.4
 */

defined( 'ABSPATH' ) || exit;

$data = isset( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="rocket-promo-banner" id="rocket-promo-banner">
	<div>
		<h3 class="rocket-promo-title">
			<span class="rocket-promo-discount">
				<?php
				// translators: %s = promotion discount percentage.
				printf( esc_html__( '%s off', 'rocket' ), esc_html( $data['discount_percent'] . '%' ) );
				?>
			</span>
			<?php
			// translators: %s = promotion name.
			printf( esc_html__( '%s promotion is live!', 'rocket' ), esc_html( $data['name'] ) );
			?>
		</h3>
		<p class="rocket-promo-message"><?php echo wp_kses_post( $data['message'] ); ?></p>
	</div>
	<div class="rocket-promo-cta-block">
		<p class="rocket-promo-deal"><?php esc_html_e( 'Hurry Up! Deal ends in:', 'rocket' ); ?></p>
		<ul class="rocket-promo-countdown" id="rocket-promo-countdown">
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days"><?php echo esc_html( $data['countdown']['days'] ); ?></span> <?php esc_html_e( 'Days', 'rocket' ); ?></li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours"><?php echo esc_html( $data['countdown']['hours'] ); ?></span> <?php esc_html_e( 'Hours', 'rocket' ); ?></li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes"><?php echo esc_html( $data['countdown']['minutes'] ); ?></span> <?php esc_html_e( 'Minutes', 'rocket' ); ?></li>
			<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds"><?php echo esc_html( $data['countdown']['seconds'] ); ?></span> <?php esc_html_e( 'Seconds', 'rocket' ); ?></li>
		</ul>
		<button class="rocket-promo-cta wpr-popin-upgrade-toggle"><?php esc_html_e( 'Upgrade now', 'rocket' ); ?></button>
	</div>
	<button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-promotion"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice', 'rocket' ); ?></span></button>
</div>
