<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists;

use WP_Rocket\Engine\Optimization\DynamicLists\AbstractDataManager;

class DataManager extends AbstractDataManager {

	/**
	 * Get cache transient name.
	 *
	 * @return string
	 */
	protected function get_cache_transient_name() {
		return 'wpr_dynamic_lists';
	}

	/**
	 * Get lists json filename.
	 *
	 * @return string
	 */
	protected function get_json_filename() {
		return 'dynamic-lists';
	}
}
