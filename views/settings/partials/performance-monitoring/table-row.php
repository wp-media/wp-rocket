<?php
/**
 * Performance monitor row.
 *
 * @since 3.20
 */

defined( 'ABSPATH' ) || exit;
?>
<tr class="wpr-pma-item wpr-speed-radar-item-result" data-rocket-pm-id="<?php echo esc_attr( $data->id ); ?>" >
	<td class="wpr-pma-item-status">
		<?php
		$this->render_performance_score( (array) $data);
		?>
	</td>

	<td class="wpr-pma-item-title">
		<a href="<?php echo esc_url( $data->url ); ?>" target="_blank" rel="noopener">
			<?php echo esc_html( $data->title ); ?>
		</a>
	</td>

	<td class="wpr-pma-item-date"><?php echo esc_html( gmdate( 'Y-m-d H:i:s', $data->modified ) ); ?></td>

	<td class="wpr-pma-item-actions">
		<?php
		$this->render_action_button(
			'button',
			'speed_radar_refresh',
			[
				'label'      => __( 'Re-test', 'rocket' ),
				'attributes' => [
					'class'      => 'wpr-icon-bold-refresh',
					'title'      => __( 'Re-test', 'rocket' ),
					'aria-label' => __( 'Re-test', 'rocket' ),
				],
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
						'class'  => 'wpr-icon-report',
						'title'  => __( 'See Report', 'rocket' ),
					],
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
					'class'                => 'wpr-icon-trash wpr-confirm-delete',
					'title'                => __( 'Delete', 'rocket' ),
					'aria-label'           => __( 'Delete', 'rocket' ),
					'data-wpr_confirm_msg' => esc_html__( 'Are you sure you want to delete this item?', 'rocket' ),
				],
			]
		);
		?>
	</td>
</tr>
