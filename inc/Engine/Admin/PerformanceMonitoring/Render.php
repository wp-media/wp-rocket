<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\PerformanceMonitoring;

use WP_Rocket\Abstract_Render;

class Render extends Abstract_Render {
	/**
	 * Render the ui part from views.
	 *
	 * @param array $items Items from database.
	 *
	 * @return void
	 */
	public function render_ui( array $items ) {
		$data = compact( 'items' );
		echo $this->generate( 'sections/performance-monitoring', $data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}

	public function render_performance_score( array $data ) {

		$data['status-color'] = $this->get_score_color_status( (int) $data['score'] );

		echo $this->generate( 'partials/performance-monitoring/performance-score', $data );
	}

	public function render_global_score_widget( array $data ) {
		echo $this->generate( 'partials/performance-monitoring/global-score-widget', $data );
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
		} elseif ( $score <= 85 ) {
			return 'status-yellow';
		} else {
			return 'status-green';
		}
	}
}
