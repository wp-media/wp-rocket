<?php
/**
 * Renewal soon banner.
 *
 * @since 3.7.5
 */

defined( 'ABSPATH' ) || exit;

$data = isset( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="rocket-renewal-banner">
	<div class="rocket-renew-message">
		<h3 class="rocket-promo-title"><?php esc_html_e( 'Your license is about to expire!', 'rocket' ); ?></h3>
		<p><?php esc_html_e( 'You will soon lose the features that keep your site fast, plus access to updates and support.', 'rocket' ); ?></p>
		<?php if ( isset( $data['more_info'] ) && $data['more_info'] ) : ?>
		<p><?php echo $data['message']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
		<?php endif; ?>
	</div>
	<ul class="rocket-promo-countdown" id="rocket-renew-countdown">
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days"><?php echo esc_html( $data['countdown']['days'] ); ?></span> <?php esc_html_e( 'Days', 'rocket' ); ?></li>
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours"><?php echo esc_html( $data['countdown']['hours'] ); ?></span> <?php esc_html_e( 'Hours', 'rocket' ); ?></li>
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes"><?php echo esc_html( $data['countdown']['minutes'] ); ?></span> <?php esc_html_e( 'Minutes', 'rocket' ); ?></li>
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds"><?php echo esc_html( $data['countdown']['seconds'] ); ?></span> <?php esc_html_e( 'Seconds', 'rocket' ); ?></li>
	</ul>
	<div class="rocket-renew-cta-container">
		<a href="<?php echo esc_url( $data['renewal_url'] ); ?>" class="rocket-renew-cta" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Renew Now', 'rocket' ); ?></a>
	</div>
</div>
