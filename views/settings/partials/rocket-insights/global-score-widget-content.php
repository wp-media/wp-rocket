<?php
/**
 * Global Score Widget Content.
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="wpr-percentage-score-widget">
	<div>
		<?php
		if ( isset( $data['status'] ) && 'no-url' !== $data['status'] ) :
			$data['is_dashboard'] = true; // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedVariableFound
			$this->render_performance_score( $data );
			?>
		<?php else : ?>
			<div class="wpr-score-no-urls"></div>
		<?php endif; ?>
	</div>
	<p class="wpr-page-num-txt">
	<?php
	// translators: %1$s is the status text, %2$s is the number of pages tracked/monitored.
	printf( '%1$s: %2$s', esc_html( $data['status_text'] ), intval( $data['pages_num'] ) );
	?>
	</p>
	<div id="wpr_global_score_widget_add_page_btn_wrapper">
		<?php
		$this->render_add_page_btn( 'global-score-widget', $data );
		?>
	</div>
</div>
