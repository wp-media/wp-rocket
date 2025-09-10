<?php
?>
<div class="wpr-optionHeader">
	<h3 class="wpr-title2"><?php esc_html_e( 'Performance Summary', 'rocket' ); ?></h3>
</div>

<?php if ( empty( $data['items'] ) ) : ?>
	<p>
		<?php esc_html_e(
			printf(
				'You can analyze up to %1s pages and run %2s test per month. Want more?',
				'1', // number of pages.
				'3', // total number of tests available.
			), 'rocket');
		?>
		<a href="#"><?php esc_html_e( 'Upgrade Now', 'rocket' ); ?></a>
	</p>
<?php else: ?>
<table class="wp-rocket-data-table widefat striped wpr-pma-urls-table">
	<tbody>
	<tr class="wpr-pma-item">
		<td class="wpr-pma-item-title"><?php esc_html_e( 'Global score', 'rocket' ); ?></td>
		<td class="wpr-pma-item-status">
			100
		</td>
		<td class="wpr-pma-item-date"></td>
		<td class="wpr-pma-item-actions"></td>
	</tr>
	<?php
	foreach ( $data['items'] as $pma_record ) {
		$this->render_parts_with_data( 'partials/performance-monitoring/pma-table-row', $pma_record );
	}
	?>

	</tbody>
</table>
<?php endif;?>
