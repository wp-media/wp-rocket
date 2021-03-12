<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use \WP_Rocket_WP_Background_Process;

class WarmupProcess extends WP_Rocket_WP_Background_Process {

	/**
	 * Background process action name.
	 *
	 * @var string
	 */
	protected $action = 'rucss_warmup_saas_call';

	/**
	 * Background process prefix.
	 *
	 * @var string
	 */

	protected $prefix = 'rocket';

	/**
	 * APIClient instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * WarmupProcess constructor.
	 *
	 * @param APIClient $api_client APIClient instance.
	 */
	public function __construct( APIClient $api_client ) {
		parent::__construct();

		$this->api_client = $api_client;
	}

	/**
	 * Background process task for each resource
	 *
	 * @param array $resource Resource array.
	 *
	 * @return bool
	 */
	protected function task( $resource ) {
		if ( ! is_array( $resource ) ) {
			return false;
		}

		return ! $this->api_client->send_warmup_request( $resource );
	}
}
