<?php
/**
 * RocketCDN small CTA template.
 *
 * @since 3.5
 *
 * @param array $data {
 *      @type string $container_class container CSS class.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );

$data = isset( $data ) && is_array( $data ) ? $data : []; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>
<div class="wpr-rocketcdn-cta-small notice-alt notice-warning <?php echo esc_attr( $data['container_class'] ); ?>" id="wpr-rocketcdn-cta-small">
	<div class="wpr-flex">
		<section>
			<h3 class="notice-title"><?php esc_html_e( 'Speed up your website with RocketCDN, WP Rocket’s Content Delivery Network.', 'rocket' ); ?></strong></h3>
		</section>
		<div>
			<button class="wpr-button" id="wpr-rocketcdn-open-cta"><?php esc_html_e( 'Learn More', 'rocket' ); ?></button>
		</div>
	</div>
</div>
