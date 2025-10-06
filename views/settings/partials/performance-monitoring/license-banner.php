<?php
/**
 * Performance Monitoring License Banner
 *
 * @package WP_Rocket
 */

defined( 'ABSPATH' ) || exit; // Exit if accessed directly.
?>

<div class="wpr-pma-license-banner">
	<div class="wpr-pma-banner-header">
		<h2><?php esc_html_e( 'Unlock Your Site’s True Performance!', 'rocket' ); ?></h2>
		<p><?php echo esc_html( $data['subtitle'] ); ?></p>
	</div>
	<div class="wpr-pma-banner-content">
	<div class="wpr-pma-benefits-list-container">
		<ul class="wpr-pma-benefits-list">
			<?php foreach ( $data['highlights'] as $wp_rocket_highlight ) : ?>
			<li>
				<span><?php echo esc_html( $wp_rocket_highlight ); ?></span>
			</li>
			<?php endforeach; ?>
		</ul>
		<p class="wpr-pma-terms">
			<?php echo esc_html( $data['billing'] ); ?>
			<?php if ( $data['price_before_discount'] ) : ?>
				<?php echo esc_html( $data['promo_billing'] ); ?>
			<?php endif; ?>
		</p>
	</div>
		<div class="wpr-pma-price-box">
			<?php if ( $data['price_before_discount'] ) : ?>
			<span class="wpr-pma-offer"><?php echo esc_html( $data['promo_name'] ); ?></span>
			<p class="wpr-pma-price-before-discount">
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
			<p class="wpr-pma-vat">
				<?php esc_html_e( 'Taxes may apply depending on your country of residence', 'rocket' ); ?>
			</p>
			<a href="<?php echo esc_url( $data['btn_url'] ); ?>" class="wpr-pma-cta-button" data-wpr_track_button="Get Performance Monitoring" data-wpr_track_context="Addons">
				<?php esc_html_e( 'GET STARTED', 'rocket' ); ?>
			</a>
		</div>
	</div>
	<div class="wpr-pma-banner-footer">
	</div>
</div>
