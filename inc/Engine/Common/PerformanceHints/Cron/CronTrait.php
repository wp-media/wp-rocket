<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Cron;

trait CronTrait {
	/**
	 * Performance Hints Deletion interval filter.
	 *
	 * @param string $filter_name The filter name.
	 *
	 * @return object
	 */
	public function deletion_interval( string $filter_name ): object {
		/**
		 * Filters the interval (in months) to determine when a performance data entry is considered 'old'.
		 * Old performance entries are eligible for deletion. By default, a performance entry is considered old if it hasn't been accessed in the last month.
		 *
		 * @param int $delete_interval The interval in months after which a performance data entry is considered old. Default is 1 month.
		 */
		$delete_interval = wpm_apply_filters_typed( 'integer', $filter_name, 1 );

		if ( $delete_interval <= 0 ) {
			return $this->queries;
		}

		return $this->queries->set_cleanup_interval( $delete_interval );
	}
}
