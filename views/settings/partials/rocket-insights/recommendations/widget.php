<?php
/**
 * Recommendations Widget container template.
 *
 * This is the main container that wraps the different recommendation states.
 *
 * @since 3.21
 *
 * @param array $data {
 *     Data for recommendations widget.
 *
 *     @type string $state             Current state: 'loading', 'completed', 'failed', 'success'.
 *     @type array  $recommendations   List of recommendation items (for completed state).
 *     @type bool   $show_load_more    Whether to show the "Load More" button.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_ri_state           = $data['state'];
$rocket_ri_recommendations = $data['recommendations'];
$rocket_ri_show_load_more  = $data['show_load_more'];
?>
<div class="wpr-recommendations" data-state="<?php echo esc_attr( $rocket_ri_state ); ?>">
	<div class="wpr-recommendations__header">
		<h3 class="wpr-recommendations__title">
			<?php esc_html_e( 'Recommendations', 'rocket' ); ?>
		</h3>
	</div>

	<div class="wpr-recommendations__content">
		<?php
		switch ( $rocket_ri_state ) {
			case 'loading':
				$this->render_part( 'rocket-insights/recommendations/states/loading' );
				break;

			case 'failed':
				$this->render_part( 'rocket-insights/recommendations/states/failed' );
				break;

			case 'completed':
				if ( empty( $rocket_ri_recommendations ) ) {
					$this->render_part( 'rocket-insights/recommendations/states/success' );
				} else {
					$this->render_parts_with_data(
						'rocket-insights/recommendations/states/completed',
						[
							'recommendations' => $rocket_ri_recommendations,
							'show_load_more'  => $rocket_ri_show_load_more,
						]
					);
				}
				break;
		}
		?>
	</div>
</div>
