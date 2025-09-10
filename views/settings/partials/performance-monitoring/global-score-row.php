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
		<?php if ( 'in-progress' == $data ['status'] ) : ?>
			<span>
					<?php
					esc_html_e(
						sprintf(
							'Tracked pages: %s',
						$data['pages_num']
						),
						'rocket'
						);
					?>
				</span>
		<?php else : ?>
			<span>
					<?php
					esc_html_e(
						sprintf(
							'%s pages monitored',
							$data['pages_num']
						),
						'rocket'
						);
					?>
				</span>
		<?php endif; ?>
	</td>
	<td class="wpr-pma-item-actions"></td>
</tr>
