<?php
/**
 * Performance monitoring addon Dashboard section template.
 *
 * @since 3.20
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php esc_html_e( 'Speed Radar', 'rocket' ); ?></h3>
</div>

<div class="wpr-field wpr-field-speed-radar">
	<table class="wpr-speed-radar-table">
		<tbody>
		<tr class="wpr-speed-radar-item">
			<td class="wpr-speed-radar-page"><?php esc_html_e( 'Global score', 'rocket' ); ?></td>
			<td class="wpr-speed-radar-status">
				100
			</td>
			<td class="wpr-speed-radar-date"></td>
			<td class="wpr-speed-radar-actions"></td>
		</tr>
		<?php foreach ( $data['items'] as $rocket_db_item ) { ?>
			<tr class="wpr-speed-radar-item">
				<td class="wpr-speed-radar-page">
					<a href="<?php echo esc_url( $rocket_db_item->url ); ?>" target="_blank" rel="noopener">
						<?php echo esc_html( $rocket_db_item->title ); ?>
					</a>
				</td>
				<td class="wpr-speed-radar-status">
					<span class="wpr-speed-radar-score wpr-speed-radar-score--good"><?php echo intval( $rocket_db_item->score ); ?></span>
				</td>
				<td class="wpr-speed-radar-date"><?php echo esc_html( gmdate( 'Y-m-d H:i:s', $rocket_db_item->modified ) ); ?></td>
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

					$this->render_action_button(
						'button',
						'gtmetrix_open',
						[
							'label'      => 'GT',
							'attributes' => [
								'target' => '_blank',
								'class'  => 'wpr-button wpr-button--small wpr-button--gray',
								'title'  => __( 'Open in GTmetrix', 'rocket' ),
							],
						]
					);

					$this->render_action_button(
						'link',
						'speed_radar_delete',
						[
							'label'      => '',
							'url'        => $rocket_db_item->delete_url(),
							'attributes' => [
								'class'            => 'wpr-icon-trash',
								'title'            => __( 'Delete', 'rocket' ),
								'aria-label'       => __( 'Delete', 'rocket' ),
								'data-wpr_onclick' => 'return confirm("' . esc_js( __( 'Are you sure you want to delete this item?', 'rocket' ) ) . '")',
							],
						]
					);
					?>
				</td>
			</tr>
			<?php
		}
		?>

		</tbody>
	</table>

	<div class="wpr-speed-radar-add">
		<input type="text"
				class="wpr-speed-radar-input"
				placeholder="<?php esc_attr_e( 'Enter a page address to monitor', 'rocket' ); ?>"
				id="wpr-speed-radar-url-input" />

		<?php
		$this->render_action_button(
			'link',
			'add_page_speed_radar',
			[
				'label'      => __( 'ADD PAGE +', 'rocket' ),
				'url'        => '#',
				'attributes' => [
					'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple',
				],
			]
		);
		?>
	</div>
</div>
