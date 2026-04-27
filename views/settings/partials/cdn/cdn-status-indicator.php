<?php
/**
 * CDN status indicator partial.
 *
 * @since 3.22
 *
 * @param array $data {
 *     Status data.
 *
 *     @type bool   $is_active   Whether CDN is active.
 *     @type string $status_text Main status text.
 *     @type string $details     Details text (edge locations, pages covered).
 *     @type string $class       CSS class for the status indicator.
 *     @type bool   $is_pause_btn_disabled Whether the pause button should be disabled.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_details               = isset( $data['details'] ) ? $data['details'] : '';
$rocket_class                 = isset( $data['class'] ) ? $data['class'] : '';
$rocket_is_pause_btn_disabled = isset( $data['disable_pause_btn'] ) ? $data['disable_pause_btn'] : false;

if ( ! $data['is_active'] ) {
	return;
}
?>

<div class="wpr-cdn-status <?php echo esc_attr( $rocket_class ); ?>">
	<div class="wpr-cdn-indicator">
		<div class="wpr-cdn-indicator__content">
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
	</div>
	<button type="button" class="wpr-cdn-pause" aria-pressed="false" <?php echo $rocket_is_pause_btn_disabled ? 'disabled' : ''; ?>>
		<span class="wpr-cdn-pause__icon"></span>
		<span class="wpr-cdn-pause__text"><?php esc_html_e( 'PAUSE CDN', 'rocket' ); ?></span>
	</button>
</div>
