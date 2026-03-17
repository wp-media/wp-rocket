<?php
/**
 * Performance monitor row.
 *
 * @since 3.20
 */

defined( 'ABSPATH' ) || exit;

$rocket_data_array                 = (array) $data;
$rocket_data_array['is_running']   = $data->is_running();
$rocket_data_array['is_dashboard'] = false;
$rocket_ri_blurred                 = '';
$rocket_img_url                    = esc_url( WP_ROCKET_ASSETS_IMG_URL );

// Get pre-formatted metrics from Render class.
$rocket_formatted_metrics = $data->formatted_metrics ?? [];
?>
<tr class="wpr-ri-item wpr-ri-item-result <?php echo esc_attr( $rocket_data_array['item_classes']['row'] ); ?>" data-rocket-insights-id="<?php echo esc_attr( $data->id ); ?>" >
	<td class="wpr-ri-item-toggle <?php echo esc_attr( $rocket_data_array['item_classes']['td'] ); ?>">
		<div class="icon-frame wpr-ri-item-toggle-single <?php echo ! $rocket_data_array['rocket_can_show_advanced_indicators'] ? 'hide' : ''; ?>">
		</div>
	</td>
	<td class="wpr-ri-item-score">
		<?php
		$this->render_performance_score( $rocket_data_array );
		?>
	</td>
	<td class="wpr-ri-item-title">
		<?php
		$rocket_css_class = $this->is_title_truncated( $data->title ) ? 'wpr-btn-with-tool-tip' : '';
		?>
		<a href="<?php echo esc_url( $data->url ); ?>" target="_blank" rel="noopener" class="<?php echo esc_attr( $rocket_css_class ); ?>">
			<span class="wpr-ri-title">
				<span class="wpr-ri-title-truncate">
					<?php echo esc_html( $data->title ); ?>
				</span>
			</span>
			<span class="wpr-ri-dot">&middot;</span>
			<span class="wpr-ri-date">
				<?php
				if ( $data->is_running() ) {
					echo esc_html( __( 'Analyzing your page (~1 min)', 'rocket' ) );
				} else {
					echo esc_html( human_time_diff( $data->modified, time() ) . ' ' . __( 'ago', 'rocket' ) );
				}
				?></span><?php // phpcs:ignore Squiz.PHP.EmbeddedPhp.ContentAfterEnd ?>
			<?php if ( '' !== $rocket_css_class ) : ?>
			<div class="wpr-tooltip">
				<div class="wpr-tooltip-content">
					<?php echo esc_html( $data->title ); ?>
				</div>
			</div>
			<?php endif; ?>
		</a>
	</td>
	<td class="wpr-ri-item-actions <?php echo esc_attr( $rocket_data_array['item_classes']['td'] ); ?>">
		<?php
		$rocket_insights_retest_button_args = [
			'label'      => __( 'Re-Test', 'rocket' ),
			'attributes' => [
				'class'       => 'wpr-icon-bold-refresh wpr-ri-action wpr-action-speed_radar_refresh',
				'aria-label'  => __( 'Re-test', 'rocket' ),
				'data-source' => 're-test add-on page',
			],
		];

		// Retest button should be disabled if the score is zero or this row is still running.
		if ( $data->is_running() || ! $data->has_credit ) {
			$rocket_insights_retest_button_args['attributes']['class']   .= ' wpr-ri-action--disabled';
			$rocket_insights_retest_button_args['attributes']['disabled'] = true;
		}

		if ( ! $data->has_credit ) {
			$rocket_insights_retest_button_args['attributes']['class']   .= ' wpr-btn-with-tool-tip';
			$rocket_insights_retest_button_args['tooltip']                = __( 'You’ve reached your free monthly plan limit. Upgrade now to unlock unlimited on-demand tests.', 'rocket' );
			$rocket_insights_retest_button_args['attributes']['disabled'] = true;
		}

		$this->render_action_button(
			'button',
			'speed_radar_refresh',
			$rocket_insights_retest_button_args
		);

		$rocket_insights_show_report_btn_args = [
			'label'      => __( 'See Report', 'rocket' ),
			'url'        => $data->report_url,
			'attributes' => [
				'target'                      => '_blank',
				'class'                       => 'wpr-ri-action wpr-ri-report',
				'data-rocket-insights-row-id' => $data->id,
			],
		];
		$rocket_report_url_icon_state         = '';
		if ( empty( $data->report_url ) ) {
			$rocket_report_url_icon_state                                 = 'wpr-ri-action--disabled';
			$rocket_insights_show_report_btn_args['attributes']['class'] .= ' ' . $rocket_report_url_icon_state;
			$rocket_insights_show_report_btn_args['attributes']['target'] = '';
			$rocket_insights_show_report_btn_args['url']                  = '';
		} elseif ( ! $data->can_access_report() ) {
			$rocket_insights_show_report_btn_args['attributes']['class'] .= ' wpr-btn-with-tool-tip wpr-ri-action--disabled';
			$rocket_insights_show_report_btn_args['attributes']['target'] = '';
			$rocket_insights_show_report_btn_args['tooltip']              = __( 'Upgrade your plan to see the report', 'rocket' );
			$rocket_insights_show_report_btn_args['url']                  = '';
		}

		$this->render_action_button(
			'link',
			'speed_radar_delete',
			[
				'label'      => '',
				'url'        => $data->delete_url(),
				'attributes' => [
					'class'      => 'wpr-btn-with-tool-tip wpr-icon-trash wpr-ri-action',
					'aria-label' => __( 'Delete', 'rocket' ),
				],
			]
		);
		?>
	</td>
</tr>
<?php if ( $rocket_data_array['rocket_can_show_advanced_indicators'] ) : ?>
<tr class="wpr-ri-details <?php echo esc_attr( $rocket_data_array['details_classes']['row'] ); ?>" id="ri_details_<?php echo $rocket_data_array['id']; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view. ?>">
	<td colspan="4" class="details-section-td <?php echo esc_attr( $rocket_data_array['details_classes']['td'] ); ?>">
		<div class="details-section">
			<div class="details-header">
				<div>
					<div class="metrics-header">
						<div class="metric-label">
							<p>LCP</p>
							<div class="info-icon">
								<img src="<?php echo $rocket_img_url;// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>ri-info.svg" alt="">
								<div class="wpr-tooltip">
									<div class="wpr-tooltip-content">
										<?php echo esc_html__( 'Time until the largest visible content element renders and the main content becomes visible.', 'rocket' ); ?>
									</div>
								</div>
							</div>
						</div>
						<div class="metric-label">
							<p>TBT</p>
							<div class="info-icon">
								<img src="<?php echo $rocket_img_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>ri-info.svg" alt="">
								<div class="wpr-tooltip">
									<div class="wpr-tooltip-content">
										<?php echo esc_html__( 'Total time the main thread is blocked before the page becomes interactive during loading.', 'rocket' ); ?>
									</div>
								</div>
							</div>
						</div>
						<div class="metric-label">
							<p>CLS</p>
							<div class="info-icon">
								<img src="<?php echo $rocket_img_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>ri-info.svg" alt="">
								<div class="wpr-tooltip">
									<div class="wpr-tooltip-content">
										<?php echo esc_html__( 'Total amount of unexpected layout shifts during page loading, affecting visual stability.', 'rocket' ); ?>
									</div>
								</div>
							</div>
						</div>
						<div class="metric-label">
							<p>TTFB</p>
							<div class="info-icon">
								<img src="<?php echo $rocket_img_url; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>ri-info.svg" alt="">
								<div class="wpr-tooltip">
									<div class="wpr-tooltip-content">
										<?php echo esc_html__( 'Time from the request until the server responds, determining how soon the page starts loading.', 'rocket' ); ?>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>

			<div class="details-content">
				<div class="detail-row">
					<div class="row-right">
						<div class="metric-values <?php echo esc_attr( $rocket_ri_blurred ); ?>">
							<?php foreach ( $rocket_formatted_metrics as $rocket_metric ) : ?>
								<div class="metric-value <?php echo esc_attr( $rocket_metric['class'] ); ?>">
									<p><?php echo esc_html( $rocket_metric['formatted'] ); ?></p>
									<?php if ( '' !== $rocket_ri_blurred ) : ?>
									<div class="wpr-tooltip">
										<div class="wpr-tooltip-content">
											<?php echo esc_html__( 'Upgrade your plan to see more details.', 'rocket' ); ?>
										</div>
									</div>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					</div>
				</div>
			</div>
			<div class="row-left">
				<div class="report-link <?php echo esc_attr( $rocket_report_url_icon_state ); ?>">
					<?php
					$this->render_action_button(
						'link',
						'gtmetrix_open',
						$rocket_insights_show_report_btn_args
					);
					?>
					<div class="icon-frame"></div>
				</div>
			</div>
		</div>
	</td>
</tr>
<?php endif; ?>
