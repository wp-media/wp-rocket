<?php
/**
 * Global Score row view.
 */

defined( 'ABSPATH' ) || exit;
?>
<tr class="wpr-pma-item wpr-global-score">
	<td class="wpr-pma-item-score">
		<?php
		$this->render_performance_score( $data );
		?>
	</td>
	<td class="wpr-pma-item-title">
		<?php if ( 'in-progress' === $data['status'] ) : ?>
			<span><?php printf( esc_html__( 'Tracked pages: %s', 'rocket' ), '<span id="rocket_pma_pages_num">' . intval( $data['pages_num'] ) . '</span>' ); ?></span>
		<?php else : ?>
			<span><?php printf( esc_html__( '%s pages monitored', 'rocket' ), '<span id="rocket_pma_pages_num">' . intval( $data['pages_num'] ) . '</span>' ); ?></span>
		<?php endif; ?>
	</td>
	<td class="wpr-pma-item-actions"></td>
</tr>
