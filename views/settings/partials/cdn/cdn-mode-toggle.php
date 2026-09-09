<?php
/**
 * CDN mode toggle partial.
 *
 * Reuses the existing wpr-radio toggle component (Addons page / Analytics opt-in)
 * to switch between CDN modes (RocketCDN Free, RocketCDN Paid, Other CDN).
 *
 * @since 3.23
 *
 * @param array $data {
 *     Toggle data.
 *
 *     @type string $id            Input/label id.
 *     @type string $cdn_mode      CDN mode identifier (rocketcdn_free|rocketcdn_paid|byocdn).
 *     @type bool   $checked       Whether this mode is currently active.
 *     @type bool   $is_forced_off Whether the toggle must be disabled.
 *     @type string $label         Accessible label for the toggle.
 *     @type string $tooltip       Tooltip text shown when the toggle is forced off.
 * }
 */

defined( 'ABSPATH' ) || exit;

/**
 * Filters whether a CDN mode toggle should be displayed at all.
 *
 * Unlike $is_forced_off (which keeps the toggle visible but disabled), this
 * lets a hosting compatibility layer remove the toggle entirely when that
 * CDN mode is not a relevant choice at all on that host (e.g. One.com,
 * which manages its own CDN and forces the BYOCDN state regardless of what
 * WP Rocket's settings say).
 *
 * @since 3.23.4
 *
 * @param bool   $display  Whether to display the toggle. Default true.
 * @param string $cdn_mode CDN mode identifier (rocketcdn_free|rocketcdn_paid|byocdn).
 */
if ( ! wpm_apply_filters_typed( 'boolean', 'rocket_display_cdn_mode_toggle', true, $data['cdn_mode'] ) ) {
	return;
}
?>

<div class="wpr-radio wpr-cdn-mode-toggle">
	<input
		type="checkbox"
		class="wpr-cdn-mode-toggle__input"
		id="<?php echo esc_attr( $data['id'] ); ?>"
		data-cdn-mode="<?php echo esc_attr( $data['cdn_mode'] ); ?>"
		<?php checked( $data['checked'] ); ?>
		<?php disabled( ! empty( $data['is_forced_off'] ) ); ?>
	/>
	<label for="<?php echo esc_attr( $data['id'] ); ?>">
		<span class="screen-reader-text"><?php echo esc_html( $data['label'] ); ?></span>
		<span class="wpr-radio-ui" data-l10n-active="<?php esc_attr_e( 'On', 'rocket' ); ?>" data-l10n-inactive="<?php esc_attr_e( 'Off', 'rocket' ); ?>"></span>
	</label>
	<?php if ( ! empty( $data['is_forced_off'] ) && ! empty( $data['tooltip'] ) ) : ?>
	<div class="wpr-tooltip">
		<div class="wpr-tooltip-content">
			<?php echo esc_html( $data['tooltip'] ); ?>
		</div>
	</div>
	<?php endif; ?>
</div>
