<?php
/**
 * Global Score Widget view.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wpr-ri-score-widget-container">
	<div class="wpr-ri-score-widget-header">
		<h3 class="wpr-ri-score-widget-header__title">
			<?php echo esc_html__( 'Rocket Insights Score', 'rocket' ); ?>
		</h3>
	</div>
	<div class="wpr-field" id="wpr_global_score_widget">
		<?php $this->render_parts_with_data( 'rocket-insights/global-score-widget-content', $data ); ?>
	</div>
</div>
