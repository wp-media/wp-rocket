<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists;

use WP_Rocket\Engine\Optimization\DynamicLists\AbstractDataManager;

class DataManager extends AbstractDataManager {

	protected function get_cache_transient_name() {
		return 'wpr_dynamic_lists';
	}

	protected function get_json_filename() {
		return 'dynamic-lists';
	}

}
