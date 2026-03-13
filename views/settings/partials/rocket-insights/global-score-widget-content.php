<?php
/**
 * Global Score Widget Content.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-percentage-score-widget wpr-ri-score-widget">
	<div class="wpr-ri-score-widget__score-section">
		<?php
		if ( isset( $data['status'] ) && 'no-url' !== $data['status'] ) :
			$data['is_dashboard'] = true; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$this->render_performance_score( $data );
			?>
		<?php else : ?>
			<div class="wpr-score-no-urls"></div>
		<?php endif; ?>
		<p class="wpr-page-num-txt">
			<?php
			// translators: %1$s is the status text, %2$s is the number of pages tracked/monitored.
			printf( '%1$s: <span>%2$s</span>', esc_html( $data['status_text'] ), intval( $data['pages_num'] ) );
			?>
		</p>
	</div>

	<?php if ( ! empty( $data['average_metrics'] ) && isset( $data['average_metrics']['largest_contentful_paint'] ) && is_array( $data['average_metrics']['largest_contentful_paint'] ) ) : ?>
	<div class="wpr-ri-score-widget__metrics">
		<div class="wpr-ri-score-widget__metrics-row">
			<?php foreach ( $data['average_metrics'] as $rocket_metric_key => $rocket_metric ) : ?>
				<div class="wpr-ri-score-widget__metric-column">
					<div class="wpr-ri-score-widget__metric-label-row">
						<span class="wpr-ri-score-widget__metric-label"><?php echo esc_html( $rocket_metric['label'] ); ?></span>
						<span class="wpr-ri-score-widget__metric-info">
							<span class="wpr-ri-score-widget__metric-info-icon"></span>
							<span class="wpr-tooltip">
								<span class="wpr-tooltip-content"><?php echo esc_html( $rocket_metric['tooltip'] ); ?></span>
							</span>
						</span>
					</div>
					<span class="wpr-ri-score-widget__metric-value wpr-ri-score-widget__metric-value--<?php echo esc_attr( str_replace( 'ri-', '', $this->metric_formatter->get_metric_class( $rocket_metric_key, $rocket_metric['value'] ) ) ); ?>">
						<?php echo esc_html( $this->metric_formatter->format_metric( $rocket_metric_key, $rocket_metric['value'] ) ); ?>
					</span>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
	<?php endif; ?>

	<div class="wpr-global-score-widget-btn-wrapper">
		<?php
		$this->render_add_page_btn( 'global-score-widget', $data );
		?>
	</div>
</div>
