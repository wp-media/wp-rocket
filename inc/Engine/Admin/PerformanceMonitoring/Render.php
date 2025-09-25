<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Credit\Manager as CreditManager;

class Render extends Abstract_Render {
	/**
	 * CreditManager instance.
	 *
	 * @var CreditManager
	 */
	private $credit_manager;

	/**
	 * PerformanceMonitoringContext instance.
	 *
	 * @var PerformanceMonitoringContext
	 */
	private $pma_context;

	/**
	 * Constructor for the Render class.
	 *
	 * Initializes the Render instance with the provided template path and CreditManager.
	 *
	 * @param string                       $template_path   Path to the template file.
	 * @param CreditManager                $credit_manager  Instance of CreditManager for managing credits.
	 * @param PerformanceMonitoringContext $pma_context Instance of PerformanceMonitoringContext for managing performance monitoring context.
	 */
	public function __construct( $template_path, CreditManager $credit_manager, PerformanceMonitoringContext $pma_context ) {
		parent::__construct( $template_path );
		$this->credit_manager = $credit_manager;
		$this->pma_context    = $pma_context;
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
		$data['has_credit']    = $this->credit_manager->has_credit();
		$data['can_add_url']   = wpm_apply_filters_typesafe( 'wpr_pm_allow_add_page', true );
		$data['reach_max_url'] = ! $data['can_add_url'];

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
		$data['has_credit']    = $this->credit_manager->has_credit();
		$data['can_add_url']   = wpm_apply_filters_typesafe( 'wpr_pm_allow_add_page', true );
		$data['reach_max_url'] = ! $data['can_add_url'];

		return $this->generate( 'partials/performance-monitoring/global-score-widget', $data );
	}

	/**
	 * Generates the HTML for a single performance monitoring list row.
	 *
	 * @param object $data The data object representing a single row (page) in the performance monitoring list.
	 * @return string The rendered HTML for the performance monitoring row.
	 */
	public function get_performance_monitoring_list_row( object $data ): string {
		$data->has_credit = $this->credit_manager->has_credit();

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
}
