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
 *     @type bool   $is_subscription_loading  Whether the subscription is currently loading.
 *     @type bool   $is_paused  Whether the CDN is paused.
 *     @type string $active_status_text Status text when CDN is active.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_details                 = isset( $data['details'] ) ? $data['details'] : '';
$rocket_class                   = isset( $data['class'] ) ? $data['class'] : '';
$rocket_active_status_text      = isset( $data['active_status_text'] ) ? $data['active_status_text'] : '';
$rocket_paused_status_text      = isset( $data['paused_status_text'] ) ? $data['paused_status_text'] : '';
$rocket_paused_details          = isset( $data['paused_details'] ) ? $data['paused_details'] : '';
$rocket_is_subscription_loading = isset( $data['is_subscription_loading'] ) ? $data['is_subscription_loading'] : false;

if ( ! $data['is_active'] ) {
	return;
}
?>

<div class="wpr-cdn-status <?php echo esc_attr( $rocket_class ); ?>"
	data-active-text="<?php echo esc_attr( $rocket_active_status_text ); ?>"
	data-paused-text="<?php echo esc_attr( $rocket_paused_status_text ); ?>"
	data-active-details="<?php echo esc_attr( $rocket_details ); ?>"
	data-paused-details="<?php echo esc_attr( $rocket_paused_details ); ?>"
	data-long-details="<?php echo strlen( $rocket_paused_details ) > 120 ? '1' : '0'; ?>"
	id="wpr_cdn_status_indicator"
>
	<div class="wpr-cdn-indicator">
		<div class="wpr-cdn-indicator__info">
			<?php if ( '' !== $data['status_text'] ) : ?>
				<div class="wpr-cdn-indicator__status">
					<?php if ( $rocket_is_subscription_loading ) : ?>
						<span class="wpr-icon-orange-loader"></span>
					<?php else : ?>
						<span class="wpr-cdn-indicator__dot"></span>
					<?php endif; ?>
					<span class="wpr-cdn-indicator__text"><?php echo esc_html( $data['status_text'] ); ?></span>
				</div>
			<?php endif; ?>
		</div>
		<?php if ( ! empty( $rocket_details ) ) : ?>
			<p class="wpr-cdn-indicator__details">
				<?php echo $rocket_details; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>
			</p>
		<?php endif; ?>
	</div>
</div>
