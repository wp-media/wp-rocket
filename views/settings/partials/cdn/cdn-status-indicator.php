<?php
/**
 * CDN status indicator partial.
 *
 * @since 3.22
 *
 * @param array $data {
 *     Status data.
 *
 *     @type bool   $is_active          Whether CDN is active.
 *     @type string $status_text        Main status text.
 *     @type string $details            Details text (edge locations, pages covered).
 *     @type string $paused_status_text Status text when CDN is paused.
 *     @type string $paused_details     Details text when CDN is paused.
 *     @type string $class              CSS class for the status indicator.
 *     @type bool   $disable_pause_btn  Whether the pause button should be disabled.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_details               = isset( $data['details'] ) ? $data['details'] : '';
$rocket_class                 = isset( $data['class'] ) ? $data['class'] : '';
$rocket_is_pause_btn_disabled = isset( $data['disable_pause_btn'] ) ? $data['disable_pause_btn'] : false;
$rocket_paused_status_text    = isset( $data['paused_status_text'] ) ? $data['paused_status_text'] : '';
$rocket_paused_details        = isset( $data['paused_details'] ) ? $data['paused_details'] : '';

if ( ! $data['is_active'] ) {
	return;
}
?>

<div class="wpr-cdn-status <?php echo esc_attr( $rocket_class ); ?>"
	data-active-text="<?php echo esc_attr( $data['status_text'] ); ?>"
	data-paused-text="<?php echo esc_attr( $rocket_paused_status_text ); ?>"
	data-active-details="<?php echo esc_attr( $rocket_details ); ?>"
	data-paused-details="<?php echo esc_attr( $rocket_paused_details ); ?>"
	data-long-details="<?php echo strlen( $rocket_paused_details ) > 120 ? '1' : '0'; ?>"
>
	<div class="wpr-cdn-indicator">
		<div class="wpr-cdn-indicator__status">
			<span class="wpr-cdn-indicator__dot"></span>
			<span class="wpr-cdn-indicator__text"><?php echo esc_html( $data['status_text'] ); ?></span>
		</div>
		<?php if ( ! empty( $rocket_details ) ) : ?>
			<p class="wpr-cdn-indicator__details">
				<?php echo esc_html( $rocket_details ); ?>
			</p>
		<?php endif; ?>
	</div>
	<button type="button" class="wpr-cdn-pause" aria-pressed="false" <?php echo $rocket_is_pause_btn_disabled ? 'disabled' : ''; ?>>
		<span class="wpr-cdn-pause__icon"></span>
		<span class="wpr-cdn-pause__text wpr-cdn-pause__text--pause"><?php esc_html_e( 'PAUSE CDN', 'rocket' ); ?></span>
		<span class="wpr-cdn-pause__text wpr-cdn-pause__text--resume"><?php esc_html_e( 'RESUME CDN', 'rocket' ); ?></span>
	</button>
</div>
