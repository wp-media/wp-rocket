<?php
/**
 * Global Score Widget view.
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="wpr_global_score_widget">
	<div class="wpr-optionHeader">
		<h3 class="wpr-title2">
			<?php echo esc_html__( 'Rocket Insights Score', 'rocket' ); ?>
		</h3>
	</div>
	<div class="wpr-fieldsContainer">
		<fieldset class="wpr-fieldsContainer-fieldset">
			<div class="wpr-field">
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
					// translators: %1$s is the number of pages tracked/monitored.
					printf( esc_html( $data['status_text'] . ': %s' ), intval( $data['pages_num'] ) );
					?>
					</p>
					<div id="wpr_global_score_widget_add_page_btn_wrapper">
						<?php
						$this->render_add_page_btn( 'global-score-widget', $data );
						?>
					</div>
				</div>
			</div>
		</fieldset>
	</div>
</div>
