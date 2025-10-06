<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Managers\Plan;

class Render extends Abstract_Render {
	/**
	 * Plan instance.
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * PerformanceMonitoringContext instance.
	 *
	 * @var PerformanceMonitoringContext
	 */
	private $pma_context;

	/**
	 * Beacon instance.
	 *
	 * @var Beacon
	 */
	private $beacon;

	/**
	 * Constructor for the Render class.
	 *
	 * Initializes the Render instance with the provided template path and CreditManager.
	 *
	 * @param string                       $template_path   Path to the template file.
	 * @param Plan                         $plan            Plan instance.
	 * @param PerformanceMonitoringContext $pma_context     Instance of PerformanceMonitoringContext for managing performance monitoring context.
	 * @param Beacon                       $beacon          Beacon instance.
	 */
	public function __construct( $template_path, Plan $plan, PerformanceMonitoringContext $pma_context, Beacon $beacon ) {
		parent::__construct( $template_path );
		$this->plan        = $plan;
		$this->pma_context = $pma_context;
		$this->beacon      = $beacon;
	}

	/**
	 * Get color status class based on performance score.
	 *
	 * @param int $score Performance score (0-100).
	 * @return string Color status class.
	 */
	public function get_score_color_status( int $score ): string {
		if ( $score <= 50 ) {
			return 'status-red';
		}
		if ( $score <= 85 ) {
			return 'status-yellow';
		}
		return 'status-green';
	}

	/**
	 * Generate global score row HTML.
	 *
	 * @param array $data Data for the global score row.
	 * @return string The rendered HTML for the global score row.
	 */
	public function get_global_score_row( array $data ) {
		$data['status_text'] = $this->get_monitoring_status_text();
		return $this->generate( 'partials/performance-monitoring/global-score-row', $data );
	}


	/**
	 * Render global score row.
	 *
	 * @param array $data Data for the global score row.
	 * @return void
	 */
	public function render_global_score_row( array $data ) {
		echo $this->get_global_score_row( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render performance urls table.
	 *
	 * @param array $data Data for the performance urls table.
	 * @return void
	 */
	public function render_pma_urls_table( array $data ) {
		$rocket_insights_beacon = $this->beacon->get_suggest( 'rocket_insights' );

		$data['has_credit']    = $this->plan->has_credit();
		$data['can_add_url']   = $this->pma_context->is_adding_page_allowed();
		$data['reach_max_url'] = ! $data['can_add_url'];
		$data['help']          = $rocket_insights_beacon;

		echo $this->generate( 'partials/performance-monitoring/urls-table', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render performance score.
	 *
	 * @param array $data Data for the performance score.
	 * @return void
	 */
	public function render_performance_score( array $data ) {

		$data['status-color'] = $this->get_score_color_status( (int) $data['score'] );

		echo $this->generate( 'partials/performance-monitoring/performance-score', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render global score widget.
	 *
	 * @param array $data Data for the widget.
	 * @return void
	 */
	public function render_global_score_widget( array $data ) {
		echo $this->get_global_score_widget( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Generate the global score widget HTML.
	 *
	 * @param array $data Data for the global score widget.
	 * @return string The rendered HTML for the global score widget.
	 */
	public function get_global_score_widget( array $data ): string {
		return $this->generate(
			'partials/performance-monitoring/global-score-widget',
			$this->prepare_global_score_widget_data( $data )
		);
	}

	/**
	 * Generate the content for the global score widget.
	 *
	 * @param array $data Data for the global score widget content.
	 * @return string The rendered HTML for the global score widget content.
	 */
	public function get_global_score_widget_content( array $data ): string {
		return $this->render_parts_with_data(
			'performance-monitoring/global-score-widget-content',
			$this->prepare_global_score_widget_data( $data ),
			true
		);
	}

	/**
	 * Retrieves the data array for the global score widget.
	 *
	 * @param array $data Input data for the global score widget.
	 * @return array The prepared data for the global score widget.
	 */
	private function prepare_global_score_widget_data( array $data ) {
		$is_adding_page_allowed = $this->pma_context->is_adding_page_allowed();

		return array_merge(
			$data,
			[
				'has_credit'    => $this->plan->has_credit(),
				'can_add_url'   => $is_adding_page_allowed,
				'reach_max_url' => ! $is_adding_page_allowed,
				'status_text'   => $this->get_monitoring_status_text(),
			]
		);
	}

	/**
	 * Generates the HTML for a single performance monitoring list row.
	 *
	 * @param object $data The data object representing a single row (page) in the performance monitoring list.
	 * @return string The rendered HTML for the performance monitoring row.
	 */
	public function get_performance_monitoring_list_row( object $data ): string {
		$data->has_credit = $this->plan->has_credit();

		return $this->generate( 'partials/performance-monitoring/table-row', $data );
	}

	/**
	 * Render the HTML for a single performance monitoring list row.
	 *
	 * @param object $data The data object representing a single row (page) in the performance monitoring list.
	 * @return void
	 */
	public function render_performance_monitoring_list_row( object $data ) {
		echo $this->get_performance_monitoring_list_row( $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the license banner section from views.
	 *
	 * @param array $data Data to render the license banner section.
	 *
	 * @return void
	 */
	public function render_license_banner_section( array $data ) {
		echo $this->generate( 'partials/performance-monitoring/license-banner', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render the plan price in the license banner section from views.
	 *
	 * @param string $price    Price value.
	 * @param string $currency Currency symbol, default is '$'.
	 * @param string $period   Billing period, default is 'month'.
	 *
	 * @return void
	 */
	public function render_license_banner_plan_price( string $price, string $currency = '$', string $period = 'month' ) {
		global $wp_locale;
		$dot   = $wp_locale->number_format['decimal_point'] ?? '.';
		$price = number_format_i18n( $price, 2 );
		$price = explode( $dot, $price );
		$data  = [
			'price_number'  => $price[0],
			'price_decimal' => $dot . $price[1],
			'currency'      => $currency,
			'period'        => $period,
		];
		echo $this->generate( 'partials/performance-monitoring/license-banner-plan-price', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Returns the appropriate monitoring status text based on schedule allowance.
	 *
	 * @return string The translated status text for monitored or tracked pages.
	 */
	private function get_monitoring_status_text(): string {
		if ( $this->pma_context->is_schedule_allowed() ) {
			return __( 'Monitored Pages', 'rocket' );
		}

		return __( 'Tracked Pages', 'rocket' );
	}

	/**
	 * Generates the appropriate "Add Page" button markup for the given UI context.
	 *
	 * @since 3.20
	 *
	 * @param string $type The context in which the button is used. Accepts 'rocket-insights' or 'global-score-widget'.
	 * @param array  $data Data to be passed to the button template.
	 *
	 * @return string The generated HTML for the "Add Page" button.
	 */
	public function get_add_page_btn( string $type, array $data ) {
		switch ( $type ) {
			case 'global-score-widget':
				$button = $this->generate( 'partials/performance-monitoring/buttons/global-score-widget', $data );
				break;

			case 'rocket-insights':
			default:
				$button = $this->generate( 'partials/performance-monitoring/buttons/rocket-insights-panel', $data );
				break;
		}

		return $button;
	}

	/**
	 * Outputs the HTML for the "Add Page" button using the provided type and data.
	 *
	 * @since 3.20
	 *
	 * @param string $type The context in which the button is used. Accepts 'rocket-insights' or 'global-score-widget'.
	 * @param array  $data Data to be passed to the button template.
	 *
	 * @return void
	 */
	public function render_add_page_btn( string $type, array $data ): void {
		echo $this->get_add_page_btn( $type, $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Truncates a title to a maximum of 35 characters and adds ellipsis if needed.
	 *
	 * @since 3.20
	 *
	 * @param string $title The original title.
	 * @return array {
	 *     @type string $truncated_title The truncated title.
	 *     @type bool   $is_truncated    Whether the title was truncated.
	 * }
	 */
	public function truncate_title( string $title ): array {
		$max_length = 35;

		if ( mb_strlen( $title ) <= $max_length ) {
			return [
				'truncated_title' => $title,
				'is_truncated'    => false,
			];
		}

		return [
			'truncated_title' => mb_substr( $title, 0, $max_length ) . '(...)',
			'is_truncated'    => true,
		];
	}
}
