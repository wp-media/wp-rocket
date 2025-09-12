<?php
/**
 * Global Score Widget view.
 */

defined( 'ABSPATH' ) || exit;
?>
<div id="wpr_global_score_widget">
	<div class="wpr-optionHeader">
		<h3 class="wpr-title2">
			<?php echo esc_html__( 'RocketInsights', 'rocket' ); ?>
		</h3>
	</div>
	<div class="wpr-fieldsContainer">
		<fieldset class="wpr-fieldsContainer-fieldset">
			<div class="wpr-field">
				<div class="wpr-percentage-score-widget">
					<div>
						<?php
						if ( isset( $data['status'] ) && 'no-url' !== $data['status'] ) :
							$this->render_performance_score( $data );
							?>
						<?php else : ?>
							<div class="wpr-score-no-urls"></div>
						<?php endif; ?>
					</div>
					<p class="wpr-page-num-txt">
					<?php
					// translators: %1$s is the number of pages monitored.
					printf( esc_html__( '%1$s page monitored.', 'rocket' ), intval( $data['pages_num'] ) );
					?>
					</p>
					<?php
					$this->render_action_button(
						'link',
						'rocket_pm_add_homepage',
						[
							'label'      => $data['pages_num'] ? __( 'Add Page', 'rocket' ) : __( 'Add Homepage', 'rocket' ),
							'url'        => '#rocket_insights',
							'attributes' => [
								'class' => 'wpr-button wpr-button--icon wpr-button--small wpr-button--purple wpr-icon-plus wpr-button--no-min-width',
							],
						]
					);
					?>
				</div>
			</div>
		</fieldset>
	</div>
</div>
