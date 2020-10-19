<?php
/**
 * Renewal soon banner.
 *
 * @since 3.7.5
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="rocket-promo-banner" id="rocket-renewal-banner">
	<div>
		<h3> <?php esc_html_e( 'Your WP Rocket license is expired!', 'rocket' ); ?></h3>
		<p>
		<?php
			// translators: %1$s = <strong>, %2$s = </strong>.
			printf(
				esc_html__( 'Your website could be much faster if it could take advantage of  our %1$snew features and enhancements.%2$s', 'rocket' ),
				'<strong>',
				'</strong>'
			);
		?>
		</p>
        <p>
		<?php
			// translators: %1$s = <strong>, %2$s = </strong>.
			printf(
				esc_html__( 'Renew your license for 1 year and get an immediate %1$s%2$s off%3$s on your renewal rate: you will only pay %1$s%4$s%3$s!', 'rocket' ),
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
    <button class="wpr-notice-close wpr-icon-close" id="rocket-dismiss-renewal"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'rocket' ); ?></span></button>
</div>
