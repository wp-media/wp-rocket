<?php
/**
 * Performance monitor row.
 *
 * @since 3.20
 */

defined( 'ABSPATH' ) || exit;
?>
<tr class="wpr-speed-radar-item wpr-speed-radar-item-result" data-rocket-pm-id="<?php echo esc_attr( $data->id ); ?>" >
	<td class="wpr-speed-radar-page">
		<a href="<?php echo esc_url( $data->url ); ?>" target="_blank" rel="noopener">
			<?php echo esc_html( $data->title ); ?>
		</a>
	</td>
	<td class="wpr-speed-radar-status">
		<span class="wpr-speed-radar-score wpr-speed-radar-score--good">
			<?php
			switch ( $data->status ) {
				case 'completed':
					echo intval( $data->score );
					break;

				default:
					echo esc_html( $data->status ); // TODO: Will likely be replaced with a loader to unify the progress inidicator.
					break;
			}
			?>
		</span>
	</td>

	<td class="wpr-speed-radar-date"><?php echo esc_html( gmdate( 'Y-m-d H:i:s', $data->modified ) ); ?></td>

	<td class="wpr-speed-radar-actions">
		<?php
		$this->render_action_button(
			'button',
			'speed_radar_refresh',
			[
				'label'      => '',
				'attributes' => [
					'class'      => 'wpr-icon-refresh',
					'title'      => __( 'Refresh', 'rocket' ),
					'aria-label' => __( 'Refresh', 'rocket' ),
				],
			]
		);

		if ( ! empty( $data->report_url ) ) {
			$this->render_action_button(
				'link',
				'gtmetrix_open',
				[
					'label'      => 'GT',
					'url'        => esc_url( $data->report_url ),
					'attributes' => [
						'target' => '_blank',
						'class'  => 'wpr-button wpr-button--small wpr-button--gray',
						'title'  => __( 'Open in GTmetrix', 'rocket' ),
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
					'class'            => 'wpr-icon-trash wpr-confirm-delete',
					'title'            => __( 'Delete', 'rocket' ),
					'aria-label'       => __( 'Delete', 'rocket' ),
					'data-wpr_confirm_msg' => esc_html__( 'Are you sure you want to delete this item?', 'rocket' ),
				],
			]
		);
		?>
	</td>
</tr>
