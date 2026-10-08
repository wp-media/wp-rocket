<?php
/**
 * Dashboard Global Score component.
 *
 * @param array $data Global score data (same data as the global score widget).
 */

defined( 'ABSPATH' ) || exit;

$rocket_has_metrics = ! empty( $data['average_metrics'] ) && isset( $data['average_metrics']['largest_contentful_paint'] ) && is_array( $data['average_metrics']['largest_contentful_paint'] );
?>
<section class="wpr-dash-score" data-context="<?php echo esc_attr( $data['context'] ?? 'dashboard' ); ?>">
	<div class="wpr-dash-section-header">
		<div class="wpr-dash-section-header__heading">
			<h3 class="wpr-dash-section-header__title"><?php esc_html_e( 'Rocket Insights Global Score', 'rocket' ); ?></h3>
			<?php if ( ! empty( $data['help'] ) ) : ?>
				<a href="<?php echo esc_url( $data['help']['url'] ); ?>" data-beacon-id="<?php echo esc_attr( $data['help']['id'] ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Dashboard" class="wpr-dash-help" target="_blank" rel="noopener noreferrer">
					<span class="wpr-dash-help__icon" aria-hidden="true"></span>
					<span class="screen-reader-text"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></span>
				</a>
			<?php endif; ?>
		</div>
		<div class="wpr-dash-section-header__actions">
			<?php
			$rocket_add_page_args = [
				'label'      => $data['pages_num'] ? __( 'Add Page', 'rocket' ) : __( 'Add Homepage', 'rocket' ),
				'parameters' => [
					'type' => 'all',
				],
				'url'        => '#rocket_insights',
				'attributes' => [
					'class'       => 'wpr-dash-score__add wpr-icon-plus wpr-ri-add-url-button',
					'data-source' => 'dashboard',
				],
			];

			// Disable the button when the page limit is reached.
			if ( $data['reach_max_url'] ) {
				$rocket_add_page_args['url']                    = '';
				$rocket_add_page_args['attributes']['class']   .= ' wpr-btn-with-tool-tip disabled';
				$rocket_add_page_args['attributes']['disabled'] = 'disabled';
				$rocket_add_page_args['tooltip']                = esc_html__( 'You have reached your maximum page limit', 'rocket' );
			}

			$this->render_action_button(
				'link',
				$data['pages_num'] ? '' : 'rocket_rocket_insights_add_homepage',
				$rocket_add_page_args
			);
			?>
		</div>
	</div>

	<div class="wpr-dash-score__card">
		<div class="wpr-dash-score__summary">
			<div class="wpr-dash-score__gauge">
				<?php
				if ( isset( $data['status'] ) && 'no-url' !== $data['status'] ) {
					$data['is_dashboard'] = true; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
					$this->render_performance_score( $data );
				} else {
					echo '<div class="wpr-score-no-urls"></div>';
				}
				?>
			</div>
			<div class="wpr-dash-score__details">
				<div class="wpr-dash-score__heading">
					<span class="wpr-dash-score__title"><?php esc_html_e( 'Rocket Insights Score', 'rocket' ); ?></span>
					<span class="wpr-dash-score__pages">
						<?php
						// translators: %1$s is the status text, %2$s is the number of pages tracked/monitored.
						printf( '%1$s: <span>%2$s</span>', esc_html( $data['status_text'] ), intval( $data['pages_num'] ) );
						?>
					</span>
				</div>
				<ul class="wpr-dash-score__legend">
					<li class="wpr-dash-score__legend-item wpr-dash-score__legend-item--good"><?php esc_html_e( '90+ Good', 'rocket' ); ?></li>
					<li class="wpr-dash-score__legend-item wpr-dash-score__legend-item--average"><?php esc_html_e( '50-89 Average', 'rocket' ); ?></li>
					<li class="wpr-dash-score__legend-item wpr-dash-score__legend-item--poor"><?php esc_html_e( '0-49 Poor', 'rocket' ); ?></li>
				</ul>
			</div>
		</div>

		<?php if ( $rocket_has_metrics ) : ?>
			<div class="wpr-dash-score__metrics">
				<?php foreach ( $data['average_metrics'] as $rocket_metric_key => $rocket_metric ) : ?>
					<div class="wpr-dash-score__metric">
						<span class="wpr-dash-score__metric-label">
							<?php echo esc_html( $rocket_metric['label'] ); ?>
							<span class="wpr-ri-score-widget__metric-info">
								<span class="wpr-ri-score-widget__metric-info-icon"></span>
								<span class="wpr-tooltip">
									<span class="wpr-tooltip-content"><?php echo esc_html( $rocket_metric['tooltip'] ); ?></span>
								</span>
							</span>
						</span>
						<span class="wpr-dash-score__metric-value wpr-dash-score__metric-value--<?php echo esc_attr( str_replace( 'ri-', '', $this->metric_formatter->get_metric_class( $rocket_metric_key, $rocket_metric['value'] ) ) ); ?>">
							<?php echo esc_html( $this->metric_formatter->format_metric( $rocket_metric_key, $rocket_metric['value'] ) ); ?>
						</span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</section>
