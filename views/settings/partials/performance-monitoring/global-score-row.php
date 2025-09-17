<?php
/**
 * Global Score row view.
 */

defined( 'ABSPATH' ) || exit;
?>
<tr class="wpr-pma-item wpr-global-score">
	<td class="wpr-pma-item-score">
		<?php
		$data['is_dashboard'] = false; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
		$this->render_performance_score( $data );
		?>
	</td>
	<td class="wpr-pma-item-title">
		<?php if ( 'in-progress' === $data['status'] ) : ?>
			<span>
				<?php
				printf(
					// translators: %s is the number of pages monitored.
					esc_html__( 'Tracked pages: %s', 'rocket' ),
					esc_html( $data['pages_num'] )
				);
				?>
			</span>
		<?php else : ?>
			<span>
				<?php
				printf(
					// translators: %s is the number of pages monitored.
					esc_html__( '%s pages monitored', 'rocket' ),
					esc_html( $data['pages_num'] )
				);
				?>
			</span>
		<?php endif; ?>
	</td>
	<td class="wpr-pma-item-actions"></td>
</tr>
