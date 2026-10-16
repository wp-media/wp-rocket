<?php
/**
 * Dashboard Recommendations component.
 *
 * @param array $data {
 *     Same data as the recommendations widget.
 *
 *     @type string $state           Current state: 'loading', 'completed', 'failed'.
 *     @type array  $recommendations List of recommendation items.
 *     @type array  $rows            Rows for the table list, built from the recommendations.
 *     @type bool   $show_load_more  Whether to show the "More Recommendations" button.
 * }
 */

defined( 'ABSPATH' ) || exit;
?>
<section class="wpr-dash-recs" data-state="<?php echo esc_attr( $data['state'] ); ?>">
	<div class="wpr-dash-section-header">
		<div class="wpr-dash-section-header__heading">
			<h3 class="wpr-dash-section-header__title"><?php esc_html_e( 'Recommendations', 'rocket' ); ?></h3>
		</div>
	</div>

	<?php
	switch ( $data['state'] ) {
		case 'loading':
			?>
			<div class="wpr-dash-recs__loading" role="status">
				<?php $this->render_part( 'rocket-insights/loading-dots' ); ?>
				<p class="wpr-dash-recs__loading-text"><?php esc_html_e( 'Recommendations loading...', 'rocket' ); ?></p>
			</div>
			<?php
			break;

		case 'failed':
			$this->render_part( 'rocket-insights/recommendations/states/failed' );
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
