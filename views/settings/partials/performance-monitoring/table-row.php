<?php
/**
 * Performance monitor row.
 *
 * @since 3.20
 */

defined( 'ABSPATH' ) || exit;
?>
<tr class="wpr-pma-item wpr-pma-item-result" data-rocket-pm-id="<?php echo esc_attr( $data->id ); ?>" >
	<td class="wpr-pma-item-score">
		<?php
		$rocket_data_array                 = (array) $data;
		$rocket_data_array['is_running']   = $data->is_running();
		$rocket_data_array['is_dashboard'] = false;
		$this->render_performance_score( $rocket_data_array );
		?>
	</td>

	<td class="wpr-pma-item-title">
		<?php
		$rocket_title_data = $this->truncate_title( $data->title );
		$rocket_css_class  = 'wpr-btn-with-tool-tip';
		if ( ! $rocket_title_data['is_truncated'] ) {
			$rocket_css_class = '';
		}
		?>
		<a href="<?php echo esc_url( $data->url ); ?>" target="_blank" rel="noopener" class="<?php echo esc_attr( $rocket_css_class ); ?>">
			<span class="wpr-pma-title"><?php echo esc_html( $rocket_title_data['truncated_title'] ); ?></span> <span class="wpr-pma-dot">.</span>
			<span class="wpr-pma-date">
				<?php
				if ( $data->is_running() ) {
					echo esc_html( __( 'Analyzing your page (~1 min)', 'rocket' ) );
				} else {
					echo esc_html( human_time_diff( $data->modified, time() ) . ' ' . __( 'ago', 'rocket' ) );
				}
				?>
			</span>
			<?php if ( $rocket_title_data['is_truncated'] ) : ?>
			<div class="wpr-tooltip">
				<div class="wpr-tooltip-content">
					<?php echo esc_html( $data->title ); ?>
				</div>
			</div>
			<?php endif; ?>
		</a>
	</td>

	<td class="wpr-pma-item-actions">
		<?php
		$rocket_pma_retest_button_args = [
			'label'      => __( 'Re-Test', 'rocket' ),
			'attributes' => [
				'class'      => 'wpr-icon-bold-refresh wpr-pma-action wpr-action-speed_radar_refresh',
				'aria-label' => __( 'Re-test', 'rocket' ),
			],
		];

		// Retest button should be disabled if the score is zero or this row is still running.
		if ( $data->is_running() || ! $data->has_credit ) {
			$rocket_pma_retest_button_args['attributes']['class']   .= ' wpr-pma-action--disabled';
			$rocket_pma_retest_button_args['attributes']['disabled'] = true;
		}

		if ( ! $data->has_credit ) {
			$rocket_pma_retest_button_args['attributes']['class']   .= ' wpr-btn-with-tool-tip';
			$rocket_pma_retest_button_args['tooltip']                = __( 'You’ve reached your free monthly plan limit. Upgrade now to unlock unlimited on-demand tests.', 'rocket' );
			$rocket_pma_retest_button_args['attributes']['disabled'] = true;
		}

		$this->render_action_button(
			'button',
			'speed_radar_refresh',
			$rocket_pma_retest_button_args
		);

		$rocket_show_report_btn_args = [
			'label'      => __( 'See Report', 'rocket' ),
			'url'        => $data->report_url,
			'attributes' => [
				'target' => '_blank',
				'class'  => 'wpr-icon-report wpr-pma-action wpr-ri-report',
			],
		];

		if ( empty( $data->report_url ) ) {
			$rocket_show_report_btn_args['attributes']['class'] .= ' wpr-pma-action--disabled';
			$rocket_show_report_btn_args['attributes']['target'] = '';
			$rocket_show_report_btn_args['url']                  = '';
		} elseif ( ! $data->can_access_report() ) {
			$rocket_show_report_btn_args['attributes']['class'] .= ' wpr-btn-with-tool-tip wpr-pma-action--disabled';
			$rocket_show_report_btn_args['attributes']['target'] = '';
			$rocket_show_report_btn_args['tooltip']              = __( 'Upgrade your plan to see the report', 'rocket' );
			$rocket_show_report_btn_args['url']                  = '';
		}

		$this->render_action_button(
			'link',
			'gtmetrix_open',
			$rocket_show_report_btn_args
		);

		$this->render_action_button(
			'link',
			'speed_radar_delete',
			[
				'label'      => '',
				'url'        => $data->delete_url(),
				'attributes' => [
					'class'      => 'wpr-btn-with-tool-tip wpr-icon-trash wpr-pma-action',
					'aria-label' => __( 'Delete', 'rocket' ),
				],
			]
		);
		?>
	</td>
</tr>
