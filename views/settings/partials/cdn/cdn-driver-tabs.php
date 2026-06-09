<?php
/**
 * CDN driver tabs partial.
 *
 * Renders the tab switcher between RocketCDN / Your own CDN.
 *
 * @param array $data {
 *     Notice data.
 *
 *     @type bool $disable_other_cdn Whether to disable the other CDN tab.
 *     @type string $cdn_type The currently active CDN type.
 *     @type bool $display_tabs Whether to display the tabs (if false, both tabs will be disabled).
 * }
 *
 * @since 3.22
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-cdn-tabs <?php echo ! $data['display_tabs'] ? 'wpr-isHidden' : ''; ?>">
	<button 
		type="button" 
		class="wpr-cdn-tabs__tab <?php echo 'rocketcdn' === $data['cdn_type'] ? 'wpr-cdn-tabs__tab--active' : ''; ?>" 
		data-cdn-driver="rocketcdn" 
		data-title="<?php esc_attr_e( 'RocketCDN', 'rocket' ); ?>"
		data-cdn-mode="<?php echo esc_attr( $data['rocketcdn_mode'] ); ?>"
		aria-describedby="wpr-cdn-driver-tooltip"
	>
		<?php esc_html_e( 'RocketCDN', 'rocket' ); ?>
	</button>

	<span class="wpr-cdn-tabs__divider"></span>

	<div class="wpr-cdn-tabs__tab-wrapper">
		<button 
			type="button" 
			class="wpr-cdn-tabs__tab <?php echo 'byocdn' === $data['cdn_type'] ? 'wpr-cdn-tabs__tab--active' : ''; ?>" 
			data-cdn-driver="your-own-cdn" 
			data-title="<?php esc_attr_e( 'Your CDN', 'rocket' ); ?>"
			data-cdn-mode="Other CDN" 
			aria-describedby="wpr-cdn-driver-tooltip"
			<?php echo $data['disable_other_cdn'] ? 'disabled' : ''; ?>
		>
			<?php esc_html_e( 'Other CDN', 'rocket' ); ?>
		</button>
	</div>
	<?php if ( $data['disable_other_cdn'] ) : ?>
		<span id="wpr-cdn-driver-tooltip" class="wpr-cdn-tabs__tooltip" role="tooltip">
			<?php esc_html_e( 'You can only use one CDN at a time', 'rocket' ); ?>
		</span>
	<?php endif; ?>
</div>
