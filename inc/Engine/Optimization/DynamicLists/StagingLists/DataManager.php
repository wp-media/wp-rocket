<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists\StagingLists;

use WP_Rocket\Engine\Optimization\DynamicLists\AbstractDataManager;

class DataManager extends AbstractDataManager {

	/**
	 * Get cache transient name.
	 *
	 * @return string
	 */
	protected function get_cache_transient_name(): string {
		return 'wpr_dynamic_lists_staging';
	}

	/**
	 * Get lists json filename.
	 *
	 * @return string
	 */
	protected function get_json_filename(): string {
		return 'dynamic-lists-staging-exclusions';
	}
}
