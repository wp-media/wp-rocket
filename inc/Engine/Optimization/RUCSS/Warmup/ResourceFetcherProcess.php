<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Logger\Logger;
use \WP_Rocket_WP_Background_Process;

class ResourceFetcherProcess extends WP_Rocket_WP_Background_Process {

	protected $action = 'rucss_warmup_resource_fetcher';
	protected $prefix = 'rocket';

	/**
	 * @inheritDoc
	 */
	protected function task( $resources ) {
		if ( ! is_array( $resources ) ){
			return false;
		}

		foreach ( $resources as $resource ) {
			//check the database if those resources added before.

		}

		return false;
	}
}
