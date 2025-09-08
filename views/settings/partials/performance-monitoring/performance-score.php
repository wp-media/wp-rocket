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
	<?php else: ?>
		<div class="wpr-percentage-circle <?php esc_html_e( $data[ 'status-color' ] ?? '' );?> <?php echo isset( $data[ 'status' ] ) && 'blurred' === $data['status'] ? 'blurred' : ''; ?>">
			<?php esc_html_e( $data[ 'score' ] );?>
		</div>
	<?php endif; ?>
</div>
