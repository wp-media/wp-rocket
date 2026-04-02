<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Abstract_Render;

/**
 * Recommendations Render class.
 *
 * Handles rendering of recommendation widget partials.
 *
 * @since 3.21
 */
class Render extends Abstract_Render {

	/**
	 * DataManager instance.
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Constructor.
	 *
	 * @param string      $template_path Path to the template file.
	 * @param DataManager $data_manager Recommendations data manager instance.
	 */
	public function __construct( string $template_path, DataManager $data_manager ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		parent::__construct( $template_path );
		$this->data_manager = $data_manager;
	}

	/**
	 * Render the recommendations widget.
	 *
	 * Determines the current state and renders the appropriate partial.
	 *
	 * @param array|false $recommendations Recommendations data or false if not cached.
	 * @param bool        $echo_output Whether to echo the output or return it as a string.
	 * @return void|string
	 */
	public function render_recommendations_widget( $recommendations, bool $echo_output = true ) {
		$html = $this->get_recommendations_widget( $recommendations );

		if ( $echo_output ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
			return;
		}

		return $html;
	}

	/**
	 * Retrieves the recommendations widget component.
	 *
	 * This method fetches the widget data and generates the HTML output
	 * for the recommendations widget using the specified template.
	 *
	 * @param array|false $cached_data Recommendations data or false if not cached.
	 * @return string The rendered HTML of the recommendations widget.
	 */
	public function get_recommendations_widget( $cached_data ): string {
		$widget_data = [
			'state'           => 'loading',
			'recommendations' => [],
			'show_load_more'  => false,
		];

		if ( false !== $cached_data ) {
			$widget_data['state']           = $this->map_status_to_state( $cached_data['status'] );
			$widget_data['recommendations'] = $this->format_recommendations( $cached_data['recommendations'] );
			$widget_data['show_load_more']  = count( $cached_data['recommendations'] ) > 3;
		}

		return $this->generate( 'partials/rocket-insights/recommendations/widget', $widget_data );
	}

	/**
	 * Map API status to widget state.
	 *
	 * @param string $status API status from DataManager.
	 * @return string Widget state: 'loading', 'completed', 'failed', 'success'.
	 */
	private function map_status_to_state( string $status ): string {
		$status_map = [
			'pending'   => 'loading',
			'loading'   => 'loading',
			'completed' => 'completed',
			'failed'    => 'failed',
		];

		return $status_map[ $status ];
	}

	/**
	 * Format recommendations data for template consumption.
	 *
	 * @param array $recommendations Raw recommendations from API.
	 * @return array Formatted recommendations.
	 */
	private function format_recommendations( $recommendations ): array {
		$formatted = [];

		foreach ( $recommendations as $recommendation ) {
			$formatted[] = [
				'option_slug'    => $recommendation['option_slug'],
				'title'          => $recommendation['title'],
				'description'    => $recommendation['description'] ?? '',
				'learn_more_url' => $recommendation['learn_more_url'] ?? '',
				'icon_slug'      => $recommendation['icon_slug'] ?? '',
				'priority'       => $recommendation['priority'] ?? '',
				'impact_tags'    => $this->extract_impact_tags( $recommendation ),
				'section'        => '#' . $this->data_manager->get_section_from_option_slug( $recommendation['option_slug'] ),
			];
		}

		return $formatted;
	}

	/**
	 * Extract impact tags from recommendation metrics.
	 *
	 * Only includes metrics that have a non-null impact value.
	 *
	 * @param array $recommendation Raw recommendation data.
	 * @return array Associative array of metric => impact value.
	 */
	private function extract_impact_tags( array $recommendation ): array {
		$impact_metrics = [
			'lcp'  => $recommendation['lcp_impact'] ?? null,
			'ttfb' => $recommendation['ttfb_impact'] ?? null,
			'cls'  => $recommendation['cls_impact'] ?? null,
			'tbt'  => $recommendation['tbt_impact'] ?? null,
		];

		// Filter out null values - only include metrics with actual impact.
		return array_filter( $impact_metrics );
	}
}
