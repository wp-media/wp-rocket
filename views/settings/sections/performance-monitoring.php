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
				<span class="wpr-infoAction wpr-icon-refresh wpr-isLoading" title="<?php esc_attr_e( 'Loading...', 'rocket' ); ?>"></span>
			</td>
			<td class="wpr-speed-radar-date"></td>
			<td class="wpr-speed-radar-actions"></td>
		</tr>
		<tr class="wpr-speed-radar-item">
			<td class="wpr-speed-radar-page"><?php esc_html_e( 'Home page', 'rocket' ); ?></td>
			<td class="wpr-speed-radar-status">
				<span class="wpr-speed-radar-score wpr-speed-radar-score--good">100</span>
			</td>
			<td class="wpr-speed-radar-date">06/19/2025</td>
			<td class="wpr-speed-radar-actions">
				<?php
				$this->render_action_button(
					'button',
					'speed_radar_refresh',
					[
						'label'      => '',
						'attributes' => [
							'class' => 'wpr-icon-refresh',
							'title' => __( 'Refresh', 'rocket' ),
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
							'class' => 'wpr-button wpr-button--small wpr-button--gray',
							'title' => __( 'Open in GTmetrix', 'rocket' ),
						],
					]
				);

				$this->render_action_button(
					'button',
					'speed_radar_delete',
					[
						'label'      => '',
						'attributes' => [
							'class' => 'wpr-icon-trash',
							'title' => __( 'Delete', 'rocket' ),
							'aria-label' => __( 'Delete', 'rocket' ),
						],
					]
				);
				?>
			</td>
		</tr>
		<tr class="wpr-speed-radar-item">
			<td class="wpr-speed-radar-page"><?php esc_html_e( 'How to get rich online in 5 days', 'rocket' ); ?></td>
			<td class="wpr-speed-radar-status">
				<span class="wpr-infoAction wpr-icon-refresh wpr-isLoading" title="<?php esc_attr_e( 'Loading...', 'rocket' ); ?>"></span>
			</td>
			<td class="wpr-speed-radar-date">06/19/2025</td>
			<td class="wpr-speed-radar-actions">
				<?php
				$this->render_action_button(
					'button',
					'speed_radar_refresh',
					[
						'label'      => '',
						'attributes' => [
							'class' => 'wpr-icon-refresh',
							'title' => __( 'Refresh', 'rocket' ),
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
							'class' => 'wpr-button wpr-button--small wpr-button--gray',
							'title' => __( 'Open in GTmetrix', 'rocket' ),
						],
					]
				);

				$this->render_action_button(
					'button',
					'speed_radar_delete',
					[
						'label'      => '',
						'attributes' => [
							'class' => 'wpr-icon-trash',
							'title' => __( 'Delete', 'rocket' ),
							'aria-label' => __( 'Delete', 'rocket' ),
						],
					]
				);
				?>
			</td>
		</tr>
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
				'url' => '#',
				'attributes' => [
					'class'  => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple',
				],
			]
		);
		?>
	</div>
</div>
