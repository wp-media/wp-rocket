<?php
/**
 * Performance Score view.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-percentage-indicator">
	<?php if ( isset( $data['status'] ) && 'in-progress' === $data['status'] ) : ?>
		<div class="wpr-loading-container">
			<img class="wpr-loading-img" src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'orange-loading.gif' ); ?>"/>
		</div>
	<?php elseif ( isset( $data['status'] ) && 'failed' === $data['status'] ) : ?>
		<div class="wpr-percentage-circle  status-red <?php echo isset( $data['status'] ) && 'blurred' === $data['status'] ? 'blurred' : ''; ?>">
			!
		</div>
	<?php else : ?>
		<div class="wpr-percentage-circle <?php echo esc_html( $data['status-color'] ?? '' ); ?> <?php echo isset( $data['status'] ) && 'blurred' === $data['status'] ? 'blurred' : ''; ?>">
			<?php echo esc_html( $data['score'] ); ?>
		</div>
	<?php endif; ?>
	<?php if ( isset( $data['status'] ) && ( 'failed' === $data['status'] || 'blurred' === $data['status'] ) ) : ?>
		<div class="wpr-tooltip">
			<?php
				$tool_tip_text = ! empty( $data['error_message'] ) ? $data['error_message'] : __( 'Upgrade your plan to see your score.', 'rocket' );
			?>
			<div class="wpr-tooltip-content">
				<?php esc_html_e( $tool_tip_text ); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
