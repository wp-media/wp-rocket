<?php
/**
 * Performance monitor row.
 *
 * @since 3.20
 */

defined( 'ABSPATH' ) || exit;
?>
<tr class="wpr-ri-item wpr-ri-item-result" data-rocket-insights-id="<?php echo esc_attr( $data->id ); ?>" >
	<td class="wpr-ri-item-score">
		<?php
		$rocket_data_array                 = (array) $data;
		$rocket_data_array['is_running']   = $data->is_running();
		$rocket_data_array['is_dashboard'] = false;
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
	<td class="wpr-ri-item-actions">
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
				'target' => '_blank',
				'class'  => 'wpr-icon-report wpr-ri-action wpr-ri-report',
			],
		];

		if ( empty( $data->report_url ) ) {
			$rocket_insights_show_report_btn_args['attributes']['class'] .= ' wpr-ri-action--disabled';
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
			'gtmetrix_open',
			$rocket_insights_show_report_btn_args
		);

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
