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
	 * @param DataManager $data_manager  DataManager instance.
	 */
	public function __construct( string $template_path, DataManager $data_manager ) {
		parent::__construct( $template_path );

		$this->data_manager = $data_manager;
	}

	/**
	 * Render the recommendations widget.
	 *
	 * Determines the current state and renders the appropriate partial.
	 *
	 * @return void
	 */
	public function render_recommendations_widget(): void {
		echo $this->get_recommendations_widget(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is properly escaped in the view.
	}

	/**
	 * Retrieves the recommendations widget component.
	 *
	 * This method fetches the widget data and generates the HTML output
	 * for the recommendations widget using the specified template.
	 *
	 * @return string The rendered HTML of the recommendations widget.
	 */
	public function get_recommendations_widget(): string {
		$widget_data = $this->get_widget_data();

		return $this->generate( 'partials/rocket-insights/recommendations/widget', $widget_data );
	}

	/**
	 * Get widget data based on current recommendation status.
	 *
	 * @return array Widget data including state and recommendations.
	 */
	private function get_widget_data(): array {
		// Default widget data.
		$widget_data = [
			'state'           => 'loading',
			'recommendations' => [],
			'show_load_more'  => false,
		];

		$cached_data = $this->data_manager->get_recommendations();

		if ( ! $cached_data ) {
			// If no cached data, return default data.
			return $widget_data;
		}

		$widget_data['state']           = $this->map_status_to_state( $cached_data['status'] );
		$recommendations                = $this->format_recommendations( $cached_data['recommendations'] );
		$widget_data['recommendations'] = $recommendations;
		$widget_data['show_load_more']  = count( $recommendations ) > 3;

		return $widget_data;
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
	private function format_recommendations( array $recommendations ): array {
		$formatted = [];

		foreach ( $recommendations as $recommendation ) {
			$formatted[] = [
				'option_slug'    => $recommendation['option_slug'],
				'title'          => $recommendation['title'],
				'description'    => $recommendation['description'],
				'learn_more_url' => $recommendation['learn_more_url'],
				'icon_slug'      => $recommendation['icon_slug'],
				'priority'       => $recommendation['priority'],
				'impact_tags'    => $this->extract_impact_tags( $recommendation ),
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
			'lcp'  => $recommendation['lcp_impact'],
			'ttfb' => $recommendation['ttfb_impact'],
			'cls'  => $recommendation['cls_impact'],
			'tbt'  => $recommendation['tbt_impact'],
		];

		// Filter out null values - only include metrics with actual impact.
		return array_filter(
			$impact_metrics,
			function ( $value ) {
				return null !== $value;
			}
		);
	}
}
