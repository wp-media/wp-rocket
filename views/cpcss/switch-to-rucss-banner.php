<?php
/**
 * Switch to Remove Unused CSS banner, displayed in the License category of the Dashboard sidebar.
 *
 * Not a .wpr-notice: those are animated away by main.js.
 *
 * @since 3.23.6
 *
 * @param array $data {
 *     Banner data.
 *
 *     @type string $message     Banner message, with <b> tags.
 *     @type string $switch_url  URL to turn on Remove Unused CSS.
 *     @type string $dismiss_url URL to keep the old option.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-rucss-switch">
	<p class="wpr-rucss-switch-message"><?php echo wp_kses( $data['message'], [ 'b' => [] ] ); ?></p>
	<p class="wpr-rucss-switch-actions">
		<a class="wpr-button wpr-rucss-switch-primary" href="<?php echo esc_url( $data['switch_url'] ); ?>"><?php esc_html_e( 'Turn on Remove Unused CSS', 'rocket' ); ?></a>
		<a class="rocket-dismiss wpr-rucss-switch-secondary" href="<?php echo esc_url( $data['dismiss_url'] ); ?>"><?php esc_html_e( 'Stay with the old option', 'rocket' ); ?></a>
	</p>
</div>
