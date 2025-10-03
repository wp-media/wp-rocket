<?php
/**
 * Global Score Widget view.
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wpr-optionHeader">
	<h3 class="wpr-title2">
		<?php echo esc_html__( 'Rocket Insights Score', 'rocket' ); ?>
	</h3>
</div>
<div class="wpr-fieldsContainer">
	<fieldset class="wpr-fieldsContainer-fieldset">
		<div class="wpr-field" id="wpr_global_score_widget">
			<?php $this->render_parts_with_data( 'rocket-insights/global-score-widget-content', $data ); ?>
		</div>
	</fieldset>
</div>
