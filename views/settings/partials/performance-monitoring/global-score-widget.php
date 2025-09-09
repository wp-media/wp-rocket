<?php
/**
 * Global Score Widget view.
 */

defined( 'ABSPATH' ) || exit;
?>
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
						<img class="wpr-score-no-urls" src="<?php echo esc_url( WP_ROCKET_ASSETS_IMG_URL . 'pma-light-pulp.svg' ); ?>"/>
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
					'',
					[
						'label'      => $data['pages_num'] ? __( 'ADD PAGE', 'rocket' ) : __( 'ADD HOME PAGE', 'rocket' ),
						'parameters' => [
							'type' => 'all',
						],
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
