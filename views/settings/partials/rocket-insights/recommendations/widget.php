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
 *     @type string $state            Current state: 'loading', 'completed', 'failed', 'success'.
 *     @type array  $recommendations  List of recommendation items (for completed state).
 *     @type bool   $show_load_more   Whether to show the "Load More" button.
 * }
 */

defined( 'ABSPATH' ) || exit;

$state           = $data['state'];
$recommendations = $data['recommendations'];
$show_load_more  = $data['show_load_more'];
?>
<div class="wpr-recommendations" data-state="<?php echo esc_attr( $state ); ?>">
	<div class="wpr-recommendations__header">
		<h3 class="wpr-recommendations__title">
			<?php esc_html_e( 'Recommendations', 'rocket' ); ?>
		</h3>
	</div>

	<div class="wpr-recommendations__content">
		<?php
		switch ( $state ) {
			case 'loading':
				$this->render_part( 'rocket-insights/recommendations/states/loading' );
				break;

			case 'failed':
				$this->render_parts_with_data( 'rocket-insights/recommendations/states/failed', $data );
				break;

			case 'completed':
				if ( empty( $recommendations ) ) {
					$this->render_part( 'rocket-insights/recommendations/states/success' );
				} else {
					$this->render_parts_with_data(
						'rocket-insights/recommendations/states/completed',
						[
							'recommendations' => $recommendations,
							'show_load_more'  => $show_load_more,
						]
					);
				}
				break;
		}
		?>
	</div>
</div>
