<?php
/**
 * Performance Monitoring License Banner
 *
 * @package WP_Rocket
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
?>

<div class="wpr-ri-license-banner">
	<div class="wpr-ri-banner-header">
	</div>
	<div class="wpr-ri-banner-content">
	<div class="wpr-ri-benefits-list-container">
		<div class="wpr-ri-banner-header">
			<h2><?php esc_html_e( 'Unlock Your Site’s True Performance!', 'rocket' ); ?></h2>
			<p><?php echo esc_html( $data['subtitle'] ); ?></p>
		</div>

		<ul class="wpr-ri-benefits-list">
			<?php foreach ( $data['highlights'] as $wp_rocket_highlight ) : ?>
			<li>
				<span><?php echo wp_kses_post( $wp_rocket_highlight ); ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
		<p class="wpr-ri-terms">
			<?php echo esc_html( $data['billing'] ); ?>
			<?php if ( $data['price_before_discount'] ) : ?>
				<?php echo esc_html( $data['promo_billing'] ); ?>
			<?php endif; ?>
		</p>
	</div>
		<div class="wpr-ri-price-box">
			<?php if ( $data['price_before_discount'] ) : ?>
			<span class="wpr-ri-offer"><?php echo esc_html( $data['promo_name'] ); ?></span>
			<p class="wpr-ri-price-before-discount">
				<?php
				printf(
				// translators: %1$s currency symbol, %2$s price before discount.
					esc_html( '%1$s%2$s' ),
					'$',
					esc_html( $data['price_before_discount'] )
				);
				?>
			</p>
			<?php endif; ?>
			<?php $this->render_license_banner_plan_price( $data['price'], '$', $data['period'] ); ?>
			<p class="wpr-ri-vat">
				<?php esc_html_e( 'Taxes may apply depending on your country of residence', 'rocket' ); ?>
			</p>
			<a href="<?php echo esc_url( $data['btn_url'] ); ?>" class="wpr-ri-cta-button" data-wpr_track_button="Get Performance Monitoring" data-wpr_track_context="Addons">
				<?php esc_html_e( 'GET STARTED', 'rocket' ); ?>
			</a>
		</div>
	</div>
	<div class="wpr-ri-banner-footer">
	</div>
</div>
