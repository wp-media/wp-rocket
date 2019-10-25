<?php
/**
 * RocketCDN status on dashboard tab template.
 *
 * @since 3.5
 *
 * @param array $data {
 *      @type string $container_class Flex container CSS class.
 *      @type string $label Content label.
 *      @type string $status_class CSS Class to display the status.
 *      @type string $status_text Text to display the subscription status.
 *      @type bool   $subscription_status Boolean identifying the subscription status.
 * }
 */

defined( 'ABSPATH' ) || die( 'Cheatin&#8217; uh?' );
?>
<div class="wpr-optionHeader">
	<h3 class="wpr-title2">Rocket CDN</h3>
</div>
<div class="wpr-field wpr-field-account">
	<div class="wpr-flex <?php echo esc_attr( $data['container_class'] ); ?>">
		<div>
			<span class="wpr-title3"><?php echo esc_html( $data['label'] ); ?></span>
			<span class="wpr-infoAccount <?php echo esc_attr( $data['status_class'] ); ?>"><?php echo esc_html( $data['status_text'] ); ?></span>
		</div>
		<?php if ( ! $data['subscription_status'] ) : ?>
		<div>
			<a href="#page_cdn" class="wpr-button"><?php esc_html_e( 'Get Rocket CDN', 'rocket' ); ?></a>
		</div>
		<?php endif; ?>
	</div>
</div>
