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
				class="wpr-pma-date"><?php echo esc_html( human_time_diff( $data->modified, current_time( 'timestamp' ) ) . ' ' . __( 'ago', 'rocket' ) ); ?>
			</span>
		</a>
	</td>

	<td class="wpr-pma-item-actions">
		<?php
		$this->render_action_button(
			'button',
			'speed_radar_refresh',
			[
				'label'      => __( 'Re-test', 'rocket' ),
				'attributes' => [
					'class'      => 'wpr-btn-with-tool-tip wpr-icon-bold-refresh wpr-pma-action', // add class `wpr-pma-action--disabled` to disable the button.
					'title'      => __( 'Re-test', 'rocket' ),
					'aria-label' => __( 'Re-test', 'rocket' ),
				],
				'tool_tip'   => __( 'Upgrade your plan to get access to Automatic Updates', 'rocket' ), // should be based on a logic.
			]
		);

		if ( ! empty( $data->report_url ) ) {
			$this->render_action_button(
				'link',
				'gtmetrix_open',
				[
					'label'      => __( 'See Report', 'rocket' ),
					'url'        => esc_url( $data->report_url ),
					'attributes' => [
						'target' => '_blank',
						'class'  => 'wpr-btn-with-tool-tip wpr-icon-report wpr-pma-action',
						'title'  => __( 'See Report', 'rocket' ),
					],
					'tool_tip'   => __( 'Upgrade your plan to get access to the Report', 'rocket' ), // should be based on a logic.
				]
			);
		}

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
