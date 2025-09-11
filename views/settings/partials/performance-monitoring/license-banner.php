<?php
/**
 * Performance Monitoring License Banner
 *
 * @package WP_Rocket
 */

if (!defined('ABSPATH')) {
	exit; // Exit if accessed directly.
}
?>

<div class="wpr-pma-license-banner">
	<div class="wpr-pma-banner-header">
		<h2><?php esc_html_e('Unlock Your Site’s True Performance!', 'rocket'); ?></h2>
		<p><?php esc_html_e('See exactly how your website performs with reliable insights on how to optimize it.', 'rocket'); ?></p>
	</div>
	<div class="wpr-pma-banner-content">
		<ul class="wpr-pma-benefits-list">
			<li><?php esc_html_e('', 'rocket'); ?></li>
			<li>
				<?php
				printf(
				// translators: %1$s: number of pages, %2$s: number of tests available.
					wp_kses_post(__('Up to <strong>%1$s pages</strong> tracked', 'rocket')),
					esc_html($data['page_number']), // number of pages.
				);
				?>
			</li>
			<li>
				<?php wp_kses_post(__('Automatic <strong>performance monitoring<strong>', 'rocket')); ?>
			</li>
			<li>
				<?php wp_kses_post(__('Unlimited <strong>on-demand tests<strong>', 'rocket')); ?>
			</li>
			<li>
				<?php wp_kses_post(__('Full GTmetrix <strong> performance reports<strong>', 'rocket')); ?>
			</li>
		</ul>
		<div class="wpr-pma-price-box">
			<p class="wpr-pma-price-before-discount">
				<?php
				// translators: %1$s currency %2$s price before discount .
				printf( esc_html__( '%1$s%2$s', 'rocket' ), esc_html( $data['currency'] ) , esc_html( $data['price_before_discount'] ) );
				?>
			</p>
			<p class="wpr-pma-price">
				<span class="wpr-currency"><?php esc_html_e($data['currency']); ?></span>
				<span class="wpr-price"><?php esc_html_e($data['price']); ?></span>
				<span class="wpr-period">/<?php esc_html_e($data['period']); ?></span>
			</p>
			<p class="wpr-pma-vat">
				<?php esc_html_e('(excl. VAT)', 'rocket'); ?>
			</p>
			<a href="#" class="wpr-pma-cta-button" data-wpr_track_button="Get Performance Monitoring" data-wpr_track_context="Addons">
				<?php esc_html_e('GET STARTED', 'rocket'); ?>
			</a>
		</div>
		<div class="wpr-pma-banner-footer">
			<p class="wpr-pma-terms">
				<?php esc_html_e('* Billed monthly. Launch price valid for the first 12 months, after which standard pricing applies. You can cancel at any time, each month started is due.', 'rocket')?>
			</p>
		</div>
	</div>
</div>
