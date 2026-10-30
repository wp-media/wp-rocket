<?php
/**
 * Dashboard Recommendations component.
 *
 * @param array $data {
 *     Same data as the recommendations widget.
 *
 *     @type string $state           Current state: 'loading', 'completed', 'failed', 'no-pages'.
 *     @type array  $recommendations List of recommendation items.
 *     @type array  $rows            Rows for the table list, built from the recommendations.
 *     @type bool   $show_load_more  Whether to show the "More Recommendations" button.
 *     @type array  $help            Beacon article data of the "Need Help?" link: 'id' and 'url'.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="wpr-dash-recs" data-state="<?php echo esc_attr( $data['state'] ); ?>">
	<div class="wpr-dash-section-header">
		<div class="wpr-dash-section-header__heading">
			<h3 class="wpr-dash-section-header__title"><?php esc_html_e( 'Recommendations', 'rocket' ); ?></h3>
		</div>
		<div class="wpr-dash-section-header__actions">
			<a href="<?php echo esc_url( $data['help']['url'] ?? '' ); ?>" data-wap-prompt="<?php echo esc_attr( sprintf( /* translators: 1: settings tab name, 2: settings section name */ __( "I need help with '%1\$s' > '%2\$s'", 'rocket' ), $data['page_title'] ?? '', $data['section_title'] ?? '' ) ); ?>" data-wpr_track_button="Need Help" data-wpr_track_context="Dashboard" class="wpr-infoAction wpr-infoAction--help wpr-icon-help" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Need Help?', 'rocket' ); ?></a>
		</div>
	</div>

	<?php
	switch ( $data['state'] ) {
		case 'loading':
			?>
			<div class="wpr-dash-recs__loading" role="status">
				<?php $this->render_part( 'rocket-insights/loading-dots' ); ?>
				<p class="wpr-dash-recs__loading-text"><?php esc_html_e( 'Analyzing your page (~1 min)', 'rocket' ); ?></p>
			</div>
			<?php
			break;

		case 'failed':
			$this->render_part( 'rocket-insights/recommendations/states/failed' );
			break;

		case 'no-pages':
			$this->render_part( 'rocket-insights/recommendations/states/no-pages' );
			break;

		case 'completed':
			if ( empty( $data['recommendations'] ) ) {
				$this->render_part( 'rocket-insights/recommendations/states/success' );
				break;
			}

			$this->render_parts_with_data(
				'table-list',
				[
					'class' => 'wpr-dash-recs__list',
					'rows'  => $data['rows'],
				]
			);

			if ( $data['show_load_more'] ) :
				?>
				<button type="button" class="wpr-dash-recs__more" aria-expanded="false">
					<span class="wpr-dash-recs__more-text"><?php esc_html_e( 'More Recommendations', 'rocket' ); ?></span>
					<span class="wpr-dash-recs__more-text"><?php esc_html_e( 'Less Recommendations', 'rocket' ); ?></span>
				</button>
				<?php
			endif;
			break;
	}
	?>
</section>
