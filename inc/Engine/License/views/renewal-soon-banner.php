<?php
/**
 * Renewal soon banner.
 *
 * @since 3.7.5
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="rocket-promo-banner">
	<ul class="rocket-promo-countdown" id="rocket-renew-countdown">
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-days"><?php echo esc_html( $data['countdown']['days'] ); ?></span> <?php esc_html_e( 'Days', 'rocket' ); ?></li>
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-hours"><?php echo esc_html( $data['countdown']['hours'] ); ?></span> <?php esc_html_e( 'Hours', 'rocket' ); ?></li>
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-minutes"><?php echo esc_html( $data['countdown']['minutes'] ); ?></span> <?php esc_html_e( 'Minutes', 'rocket' ); ?></li>
		<li class="rocket-countdown-item"><span class="rocket-countdown-value rocket-countdown-seconds"><?php echo esc_html( $data['countdown']['seconds'] ); ?></span> <?php esc_html_e( 'Seconds', 'rocket' ); ?></li>
	</ul>
	<div>
		<p>
			<?php
			// translators: %1$s = <strong>, %2$s = </strong>.
			printf(
				esc_html__( 'Your %1$sWP Rocket license is about to expire.%2$s', 'rocket' ),
				'<strong>',
				'</strong>'
			);
			?>
		</p>
		<p>
		<?php
			// translators: %1$s = <strong>, %2$s = </strong>.
			printf(
				esc_html__( 'Renew with a %1$s%2$s discount%3$s before it is too late, you will only pay %1$s%4$s%3$s!', 'rocket' ),
				'<strong>',
				esc_html( $data['discount_percent'] . '%' ),
				'</strong>',
				esc_html( '$' . $data['discount_price'] )
			);
		?>
		</p>
	</div>
	<div class="rocket-promo-cta-block">
		<a href="<?php echo esc_url( $data['renewal_url'] ); ?>" class="rocket-promo-cta"><?php esc_html_e( 'Renew now', 'rocket' ); ?></a>
	</div>
</div>
