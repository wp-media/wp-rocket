<?php
/**
 * Recommendations Completed State template.
 *
 * Shown when recommendations have been fetched and there are items to display.
 *
 * @since 3.21
 *
 * @param array $data {
 *     Data for completed state.
 *
 *     @type array $recommendations List of recommendation items.
 *     @type bool  $show_load_more  Whether to show the "Load More" button.
 * }
 */

defined( 'ABSPATH' ) || exit;

$rocket_ri_recommendations = $data['recommendations'];
$rocket_ri_show_load_more  = $data['show_load_more'];
?>
<div class="wpr-recommendations__list">
	<?php
	foreach ( $rocket_ri_recommendations as $rocket_ri_recommendation ) :
		$this->render_parts_with_data(
			'rocket-insights/recommendations/item',
			$rocket_ri_recommendation
		);
	endforeach;
	?>
</div>

<?php if ( $rocket_ri_show_load_more ) : ?>
	<button type="button" class="wpr-recommendations__load-more" id="wpr-recommendations-load-more">
		<span class="wpr-recommendations__load-more-text">
			<?php esc_html_e( 'More Recommendations', 'rocket' ); ?>
		</span>
		<span class="wpr-recommendations__load-more-text">
			<?php esc_html_e( 'Less Recommendations', 'rocket' ); ?>
		</span>
	</button>
<?php endif; ?>
