<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Abstract_Render;

class Render extends Abstract_Render {

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
	 * Render the settings section from views.
	 *
	 * @param array $data Data to render the settings section.
	 *
	 * @return void
	 */
	public function render_settings_section( array $data ) {
		echo $this->generate( 'partials/performance-monitoring/settings', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}
