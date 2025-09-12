<?php
/**
 * Performance Score view.
 */

defined( 'ABSPATH' ) || exit;

$rocket_pma_item_is_blurred = false;
if ( ( isset( $data['is_blurred'] ) && $data['is_blurred'] ) || ( isset( $data['status'] ) && 'blurred' === $data['status'] ) ) {
	$rocket_pma_item_is_blurred = true;
}
?>
<div class="wpr-percentage-indicator">
	<?php if ( isset( $data['status'] ) && 'in-progress' === $data['status'] ) : ?>
		<div class="wpr-loading-container">
			<img class="wpr-loading-img" src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'orange-loading.gif' ); ?>"/>
		</div>
	<?php elseif ( isset( $data['status'] ) && 'failed' === $data['status'] ) : ?>
		<div class="wpr-percentage-circle  status-red <?php echo $rocket_pma_item_is_blurred ? 'blurred' : ''; ?>">
			!
		</div>
	<?php else : ?>
		<div class="wpr-percentage-circle <?php echo esc_html( $data['status-color'] ?? '' ); ?> <?php echo $rocket_pma_item_is_blurred ? 'blurred' : ''; ?>">
			<?php echo esc_html( $data['score'] ); ?>
		</div>
	<?php endif; ?>
	<?php if ( ( isset( $data['status'] ) && 'failed' === $data['status'] ) || $rocket_pma_item_is_blurred ) : ?>
		<div class="wpr-tooltip">
			<div class="wpr-tooltip-content">
				<?php echo ! empty( $data['error_message'] ) ? esc_html( $data['error_message'] ) : esc_html__( 'Upgrade your plan to see your score.', 'rocket' ); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
