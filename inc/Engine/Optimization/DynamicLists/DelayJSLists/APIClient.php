<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists\DelayJSLists;

use WP_Rocket\Engine\Optimization\DynamicLists\AbstractAPIClient;

class APIClient extends AbstractAPIClient {

	/**
	 * Specify API endpoint path.
	 *
	 * @return string
	 */
	protected function get_api_path() {
		return 'delay-js-exclusions/list';
	}

}
