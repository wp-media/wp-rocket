<?php
/**
 * Global Score row view.
 */

defined( 'ABSPATH' ) || exit;
?>
<tr class="wpr-ri-item wpr-global-score">
	<td class="wpr-ri-item-toggle">
		<div class="icon-frame wpr-ri-item-toggle-all">
			<img src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL ); ?>ri-caret-up-down.svg" alt="">
		</div>
	</td>
	<td class="wpr-ri-item-score">
		<?php
		$data['is_dashboard'] = false; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$this->render_performance_score( $data );
		?>
	</td>
	<td class="wpr-ri-item-title">
		<span>
			<?php
			printf(
				// translators: %s is the number of pages tracked/monitored.
				esc_html( $data['status_text'] . ': %s' ),
				esc_html( $data['pages_num'] )
			);
			?>
		</span>
	</td>
	<td class="wpr-ri-item-actions"></td>
</tr>
