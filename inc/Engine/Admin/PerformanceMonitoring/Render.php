<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Abstract_Render;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Credit\Manager as CreditManager;

class Render extends Abstract_Render {
	/**
	 * @var
	 */
	private $credit_manager;

	public function __construct( $template_path, CreditManager $credit_manager ) {
		parent::__construct( $template_path );
		$this->credit_manager = $credit_manager;
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
	 * Render global score row.
	 *
	 * @param array $data Data for the global score row.
	 * @return void
	 */
	public function render_global_score_row( array $data ) {
		echo $this->generate( 'partials/performance-monitoring/global-score-row', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	/**
	 * Render performance urls table.
	 *
	 * @param array $data Data for the performance urls table.
	 * @return void
	 */
	public function render_pma_urls_table( array $data ) {
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
		return $this->generate( 'partials/performance-monitoring/global-score-widget', $data );
	}

	/**
	 * Generates the HTML for a single performance monitoring list row.
	 *
	 * @param object $data The data object representing a single row (page) in the performance monitoring list.
	 * @return string The rendered HTML for the performance monitoring row.
	 */
	public function get_performance_monitoring_list_row( object $data ): string {
		return $this->generate( 'partials/performance-monitoring/table-row', $data );
	}

	/**
	 * Render the settings section from views.
	 *
	 * @param array $data Data to render the settings section.
	 *
	 * @return void
	 */
	public function render_settings_section( array $data ) {
		echo $this->generate( 'partials/performance-monitoring/settings', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
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
	 * Check if the retest button is enabled or not
	 *
	 * @param object $item DB row item.
	 * @return bool
	 */
	public function is_retest_btn_enabled( $item ) {
		return ! $item->is_running() && $this->has_credit();
	}

	/**
	 * Check there is a credit or not.
	 *
	 * @return bool
	 */
	public function has_credit() {
		return $this->credit_manager->has_credit();
	}
}
