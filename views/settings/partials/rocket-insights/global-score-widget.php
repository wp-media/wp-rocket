<?php
/**
 * Global Score Widget view.
 */

defined( 'ABSPATH' ) || exit;

$context = isset( $data['context'] ) ? $data['context'] : 'sidebar'; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
?>

<div class="wpr-global-score-widget-wrapper" data-context="<?php echo esc_attr( $context ); ?>">
	<div class="wpr-optionHeader">
		<h3 class="wpr-title2">
			<?php echo esc_html__( 'Rocket Insights Score', 'rocket' ); ?>
		</h3>
	</div>
	<div class="wpr-fieldsContainer">
		<fieldset class="wpr-fieldsContainer-fieldset">
			<div class="wpr-field wpr-global-score-widget">
				<?php $this->render_parts_with_data( 'rocket-insights/global-score-widget-content', $data ); ?>
			</div>
		</fieldset>
	</div>
</div>
