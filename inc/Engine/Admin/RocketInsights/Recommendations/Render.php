<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\Overrides\Overrides;
use WP_Rocket\Engine\Admin\Beacon\Beacon;

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
	 * Recommendation overrides registry.
	 *
	 * @var Overrides
	 */
	private $overrides;

	/**
	 * Beacon instance.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Constructor.
	 *
	 * @param string      $template_path Path to the template file.
	 * @param DataManager $data_manager Recommendations data manager instance.
	 * @param Overrides   $overrides Recommendation overrides registry.
	 * @param Beacon      $beacon Beacon instance.
	 */
	public function __construct( string $template_path, DataManager $data_manager, Overrides $overrides, Beacon $beacon ) {
		parent::__construct( $template_path );
		$this->data_manager = $data_manager;
		$this->overrides    = $overrides;
		$this->beacon       = $beacon;
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
		return $this->generate( 'partials/rocket-insights/recommendations/widget', $this->get_widget_data( $cached_data ) );
	}

	/**
	 * Render the redesigned recommendations component displayed on the dashboard.
	 *
	 * @param array|false $recommendations Recommendations data or false if not cached.
	 * @return void
	 */
	public function render_dashboard_recommendations( $recommendations ): void {
		echo $this->get_dashboard_recommendations( $recommendations ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Dynamic content is escaped in the view.
	}

	/**
	 * Generate the HTML of the redesigned recommendations component displayed on the dashboard.
	 *
	 * @param array|false $recommendations Recommendations data or false if not cached.
	 * @return string
	 */
	public function get_dashboard_recommendations( $recommendations ): string {
		$data = $this->get_widget_data( $recommendations );

		// The dashboard shows the first 5 recommendations; the legacy widget shows 3.
		$data['show_load_more'] = count( $data['recommendations'] ) > 5;

		$data['rows']          = array_map( [ $this, 'build_dashboard_row' ], $data['recommendations'] );
		$data['help']          = $this->beacon->get_suggest( 'rocket_insights' );
		$data['page_title']    = __( 'Dashboard', 'rocket' );
		$data['section_title'] = __( 'Recommendations', 'rocket' );

		/** This filter is documented in inc/Engine/Admin/Settings/Render.php */
		if ( ! wpm_apply_filters_typed( 'boolean', 'rocket_ai_assistant_available', true ) ) {
			$data['help'] = [];
		}

		return $this->generate( 'partials/rocket-insights/dashboard-recommendations', $data );
	}

	/**
	 * Build the data passed to the recommendations templates.
	 *
	 * @param array|false $cached_data Recommendations data or false if not cached.
	 * @return array
	 */
	private function get_widget_data( $cached_data ): array {
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

		// Without tracked pages, DataManager::save_empty_recommendations() caches a 'failed' status: override it so it isn't shown as an error.
		if ( ! $this->data_manager->has_tracked_pages() ) {
			$widget_data['state']           = 'no-pages';
			$widget_data['recommendations'] = [];
			$widget_data['show_load_more']  = false;
		}

		return $widget_data;
	}

	/**
	 * Build the table-list-row data of a recommendation, used by the dashboard component.
	 *
	 * @param array $recommendation Formatted recommendation.
	 * @return array Row data for the table-list-row partial.
	 */
	private function build_dashboard_row( array $recommendation ): array {
		return [
			'class'   => 'wpr-dash-recs__item',
			'columns' => [
				[
					'content' => $this->generate( 'partials/rocket-insights/dashboard-recommendation-row', $recommendation ),
				],
				[
					'content' => $this->generate( 'partials/rocket-insights/dashboard-recommendation-activate', $recommendation ),
					'type'    => 'actions',
				],
			],
		];
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

		return $this->overrides->apply_all( $formatted );
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
