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
		<?php
		foreach ( $data['items'] as $rocket_db_item ) {
			$this->render_parts_with_data( 'performance-monitoring-row', $rocket_db_item );
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
					'id'    => 'add_page_speed_radar',
				],
			]
		);
		?>
	</div>
</div>
