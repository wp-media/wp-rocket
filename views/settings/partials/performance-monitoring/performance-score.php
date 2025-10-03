<?php
/**
 * Performance Score view.
 */

defined( 'ABSPATH' ) || exit;

$rocket_pma_item_is_blurred = false;
$rocket_opening_anchor_tag  = '';
$rocket_closing_anchor_tag  = '';

if ( ( isset( $data['is_blurred'] ) && $data['is_blurred'] ) || ( isset( $data['status'] ) && 'blurred' === $data['status'] ) ) {
	$rocket_pma_item_is_blurred = true;
	$rocket_opening_anchor_tag  = $data['is_dashboard'] ? '<a href="' . esc_url( admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG ) . '#rocket_insights' ) . '">' : '';
	$rocket_closing_anchor_tag  = $data['is_dashboard'] ? '</a>' : '';
}

?>
<div class="wpr-percentage-indicator">
	<?php if ( ( isset( $data['status'] ) && 'in-progress' === $data['status'] ) || ! empty( $data['is_running'] ) ) : ?>
		<div class="wpr-loading-container">
			<img class="wpr-loading-img" src="<?php echo esc_url( rocket_get_constant( 'WP_ROCKET_ASSETS_IMG_URL', '' ) . 'orange-loading.svg' ); ?>"/>
		</div>
	<?php elseif ( isset( $data['status'] ) && 'failed' === $data['status'] ) : ?>
		<?php echo $rocket_opening_anchor_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="wpr-percentage-circle  status-red <?php echo $rocket_pma_item_is_blurred ? 'blurred' : ''; ?>">
				<span class="wpr-failed-score wpr-icon-exclamation"></span>
			</div>
		<?php echo $rocket_closing_anchor_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php else : ?>
		<?php echo $rocket_opening_anchor_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			<div class="wpr-percentage-circle <?php echo esc_html( $data['status-color'] ?? '' ); ?> <?php echo $rocket_pma_item_is_blurred ? 'blurred' : ''; ?> <?php echo 100 === $data['score'] ? 'wpr-centralize-100-score' : ''; ?>">
				<?php echo esc_html( $data['score'] ); ?>
			</div>
		<?php echo $rocket_closing_anchor_tag; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
	<?php endif; ?>
	<?php if ( ( isset( $data['status'] ) && 'failed' === $data['status'] ) || $rocket_pma_item_is_blurred ) : ?>
		<div class="wpr-tooltip">
			<div class="wpr-tooltip-content">
				<?php echo 'failed' === $data['status'] ? esc_html__( 'Something went wrong with this URL', 'rocket' ) : esc_html__( 'Upgrade your plan to see your score', 'rocket' ); ?>
			</div>
		</div>
	<?php endif; ?>
</div>
