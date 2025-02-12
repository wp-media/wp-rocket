<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Common\PerformanceHints\Cron;

trait FilterTrait {
	/**
	 * Preload Fonts Deletion interval filter.
	 *
	 * @param string $filter The filter name.
	 *
	 * @return int
	 */
	public function deletion_interval( string $filter ): int {
		/**
		 * Filters the interval (in months) to determine when a performance data entry is considered 'old'.
		 * Old performance entries are eligible for deletion. By default, a performance entry is considered old if it hasn't been accessed in the last month.
		 *
		 * @param int $delete_interval The interval in months after which a performance data entry is considered old. Default is 1 month.
		 */
		return wpm_apply_filters_typed( 'integer', $filter, 1 );
	}
}
