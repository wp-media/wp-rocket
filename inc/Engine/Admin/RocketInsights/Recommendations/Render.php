<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Rocket\Abstract_Render;
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
	 * @param Beacon      $beacon Beacon instance.
	 */
	public function __construct( string $template_path, DataManager $data_manager, Beacon $beacon ) { // phpcs:ignore Generic.CodeAnalysis.UselessOverridingMethod.Found
		parent::__construct( $template_path );
		$this->data_manager = $data_manager;
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
		return $this->generate(
			'partials/rocket-insights/dashboard-recommendations',
			$this->get_widget_data( $recommendations )
		);
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
			'help'            => $this->beacon->get_suggest( 'rocket_insights' ),
		];

		if ( false !== $cached_data ) {
			$widget_data['state']           = $this->map_status_to_state( $cached_data['status'] );
			$widget_data['recommendations'] = $this->format_recommendations( $cached_data['recommendations'] );
			$widget_data['rows']            = array_map( [ $this, 'build_dashboard_row' ], $widget_data['recommendations'] );
			$widget_data['show_load_more']  = count( $cached_data['recommendations'] ) > 3;
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
		$impact = '';

		if ( ! empty( $recommendation['impact_tags'] ) ) {
			$tags = '';

			foreach ( $recommendation['impact_tags'] as $metric => $value ) {
				$tags .= '<span class="wpr-dash-recs__tag" data-impact-value="' . esc_attr( (string) $value ) . '">' . esc_html( $metric ) . '</span>';
			}

			$impact = '<span class="wpr-dash-recs__impact"><span class="wpr-dash-recs__impact-label">' . esc_html__( 'Impact on', 'rocket' ) . '</span>' . $tags . '</span>';
		}

		$more_info = '';

		if ( ! empty( $recommendation['learn_more_url'] ) ) {
			$more_info = ' <a href="' . esc_url( $recommendation['learn_more_url'] ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'More info', 'rocket' ) . '</a>';
		}

		$content = '<div class="wpr-dash-recs__heading"><span class="wpr-dash-recs__title">' . esc_html( $recommendation['title'] ) . '</span>' . $impact . '</div>'
			. '<p class="wpr-dash-recs__description">' . esc_html( $recommendation['description'] ) . $more_info . '</p>';

		$activate = '<a class="wpr-dash-recs__activate wpr-recommendation-item__activate" href="' . esc_url( $recommendation['section'] ) . '" data-recommendation="' . esc_attr( $recommendation['option_slug'] ) . '">' . esc_html__( 'Activate', 'rocket' ) . '</a>';

		return [
			'class'   => 'wpr-dash-recs__item',
			'columns' => [
				[
					'content' => $content,
				],
				[
					'content' => $activate,
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
