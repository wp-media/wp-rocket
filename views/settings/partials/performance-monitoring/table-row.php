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
		$this->render_performance_score( (array) $data );
		?>
	</td>

	<td class="wpr-pma-item-title">
		<a href="<?php echo esc_url( $data->url ); ?>" target="_blank" rel="noopener">
			<span class="wpr-pma-title"><?php echo esc_html( $data->title ); ?></span> <span class="wpr-pma-dot">.</span>
			<span
				class="wpr-pma-date"><?php echo esc_html( human_time_diff( $data->modified, time() ) . ' ' . __( 'ago', 'rocket' ) ); ?>
			</span>
		</a>
	</td>

	<td class="wpr-pma-item-actions">
		<?php
		$rocket_pma_retest_button_args = [
			'label'      => __( 'Re-test', 'rocket' ),
			'attributes' => [
				'class'      => 'wpr-icon-bold-refresh wpr-pma-action wpr-action-speed_radar_refresh', // add class `wpr-pma-action--disabled` to disable the button.
				'title'      => __( 'Re-test', 'rocket' ),
				'aria-label' => __( 'Re-test', 'rocket' ),
			],
		];

		// Retest button should be disabled if the score is zero or this row is still running.
		if ( ! $this->is_retest_btn_enabled( $data ) ) {
			$rocket_pma_retest_button_args['attributes']['class'] .= ' wpr-pma-action--disabled';
			$rocket_pma_retest_button_args['disabled']             = true;
		}

		if ( ! $this->has_credit() ) {
			$rocket_pma_retest_button_args['attributes']['class'] .= ' wpr-btn-with-tool-tip';
			$rocket_pma_retest_button_args['tool_tip']             = __( 'Upgrade your plan to get access to Automatic Updates', 'rocket' );
			$rocket_pma_retest_button_args['disabled']             = true;
		}

		$this->render_action_button(
			'button',
			'speed_radar_refresh',
			$rocket_pma_retest_button_args
		);

		$rocket_show_report_btn_args = [
			'label'      => __( 'See Report', 'rocket' ),
			'url'        => esc_url( $data->report_url ?? '#' ),
			'attributes' => [
				'target' => '_blank',
				'class'  => 'wpr-icon-report wpr-pma-action',
				'title'  => __( 'See Report', 'rocket' ),
			],
		];

		if ( ! $data->can_access_report() ) {
			$rocket_show_report_btn_args['attributes']['class'] .= ' wpr-btn-with-tool-tip wpr-pma-action--disabled';
			$rocket_show_report_btn_args['attributes']['target'] = '';
			$rocket_show_report_btn_args['disabled']             = true;
			$rocket_show_report_btn_args['tool_tip']             = __( 'Upgrade your plan to get access to the Report', 'rocket' );

		}

		if ( empty( $data->report_url ) ) {
			$rocket_show_report_btn_args['attributes']['class'] .= ' wpr-pma-action--disabled';
			$rocket_show_report_btn_args['attributes']['target'] = '';
			$rocket_show_report_btn_args['disabled']             = true;
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
					'class'                => 'wpr-btn-with-tool-tip wpr-icon-trash wpr-pma-action wpr-confirm-delete',
					'title'                => __( 'Delete', 'rocket' ),
					'aria-label'           => __( 'Delete', 'rocket' ),
					'data-wpr_confirm_msg' => esc_html__( 'Are you sure you want to delete this item?', 'rocket' ),
				],
			]
		);
		?>
	</td>
</tr>
