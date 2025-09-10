<?php
/**
 * Performance Monitoring URLs Table partial.
 *
 * @since 3.20
 *
 * @var array $data {
 *     Data for the performance monitoring URLs table.
 *
 *     @type array $items List of performance monitoring records.
 * }
 */
defined('ABSPATH') || exit;
?>
<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php esc_html_e('Performance Summary', 'rocket'); ?></h3>
</div>

<?php if ( empty($data['items']) || 'in-progress' == $data[ 'global_score' ] [ 'status' ] ) : ?>
	<p class="wpr-pma-summary-info">
		<?php _e(
			sprintf(
				'You can analyze up to <strong>%1$s pages</strong> and run <strong>%2$s test per month.</strong> Want more? ',
				'1', // number of pages.
				'3', // total number of tests available.
			), 'rocket');
		?>
		<a href="#"><?php esc_html_e('Upgrade Now', 'rocket'); ?></a>
	</p>
<?php endif; ?>

<?php if (!empty($data['items'])) : ?>
	<table class="wp-rocket-data-table widefat wpr-pma-urls-table">
		<tbody>
		<?php
		$this->render_global_score_row( $data[ 'global_score' ] );
		?>
		<?php
		foreach ($data['items'] as $pma_record) {
			$this->render_parts_with_data('performance-monitoring/table-row', $pma_record);
		}
		?>

		</tbody>
	</table>
<?php endif; ?>

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
