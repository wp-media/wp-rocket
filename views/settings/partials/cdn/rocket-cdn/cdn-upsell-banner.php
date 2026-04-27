<?php
/**
 * CDN upsell banner partial.
 *
 * Displayed on the Built-in CDN (free) view to encourage upgrading to RocketCDN Unlimited.
 *
 * @since 3.21.2
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wpr-cdn-upsell">
	<p class="wpr-cdn-upsell__text">
		<strong><?php esc_html_e( 'Need full-site Content Delivery coverage?', 'rocket' ); ?></strong>
		<?php esc_html_e( 'Extend RocketCDN to all your pages with unlimited bandwidth.', 'rocket' ); ?>
		<a href="#" class="wpr-cdn-upsell__link">
			<?php esc_html_e( 'Upgrade Now', 'rocket' ); ?>
		</a>
	</p>
</div>
