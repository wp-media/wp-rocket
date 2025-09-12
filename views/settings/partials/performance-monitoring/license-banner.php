<?php
/**
 * Performance Monitoring License Banner
 *
 * @package WP_Rocket
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}
?>

<div class="wpr-pma-license-banner">
	<div class="wpr-pma-banner-header">
		<h2><?php esc_html_e( 'Unlock Your Site’s True Performance!', 'rocket' ); ?></h2>
		<p><?php esc_html_e( 'See how your top pages perform and quickly spot and optimize what slows your site down.', 'rocket' ); ?></p>
	</div>
	<div class="wpr-pma-banner-content">
	<div class="wpr-pma-benefits-list-container">
		<ul class="wpr-pma-benefits-list">
			<li>
				<span>
				<?php
				printf(
				// translators: %1$s: number of pages.
					wp_kses_post( __( 'Up to <strong>%1$s pages</strong> tracked', 'rocket' ) ),
					esc_html( $data['page_number'] ), // number of pages.
				);
				?>
				</span>
			</li>
			<li>
				<span><?php echo wp_kses_post( __( 'Automatic <strong>performance monitoring</strong>', 'rocket' ) ); ?></span>
			</li>
			<li>
				<span><?php echo wp_kses_post( __( 'Unlimited <strong>on-demand tests</strong>', 'rocket' ) ); ?></span>
			</li>
			<li>
				<span><?php echo wp_kses_post( __( 'Full GTmetrix <strong>performance reports</strong>', 'rocket' ) ); ?></span>
			</li>
		</ul>
		<p class="wpr-pma-terms">
			<?php esc_html_e( '* Billed monthly. Launch price valid for the first 12 months, after which standard pricing applies. You can cancel at any time, each month started is due.', 'rocket' ); ?>
		</p>
	</div>
		<div class="wpr-pma-price-box">
			<span class="wpr-pma-offer"><?php esc_html_e( 'Launch Offer', 'rocket' ); ?></span>
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
			<?php $this->render_license_banner_plan_price( $data['price'], '$', $data['period'] ); ?>
			<p class="wpr-pma-vat">
				<?php esc_html_e( '(excl. VAT)', 'rocket' ); ?>
			</p>
			<a href="#" class="wpr-pma-cta-button" data-wpr_track_button="Get Performance Monitoring" data-wpr_track_context="Addons">
				<?php esc_html_e( 'GET STARTED', 'rocket' ); ?>
			</a>
		</div>
	</div>
	<div class="wpr-pma-banner-footer">
	</div>
</div>
