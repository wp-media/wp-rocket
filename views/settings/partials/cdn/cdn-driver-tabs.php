<?php
/**
 * CDN driver tabs partial.
 *
 * Renders the tab switcher between RocketCDN / Your own CDN.
 *
 * @since 3.22
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="wpr-cdn-tabs">
	<button type="button" class="wpr-cdn-tabs__tab wpr-cdn-tabs__tab--active" data-cdn-driver="rocketcdn" data-title="<?php esc_attr_e( 'RocketCDN', 'rocket' ); ?>">
		<?php esc_html_e( 'RocketCDN', 'rocket' ); ?>
	</button>
	<span class="wpr-cdn-tabs__divider"></span>
	<button type="button" class="wpr-cdn-tabs__tab" data-cdn-driver="your-own-cdn" data-title="<?php esc_attr_e( 'Your CDN', 'rocket' ); ?>">
		<?php esc_html_e( 'Your own CDN', 'rocket' ); ?>
	</button>
</div>
