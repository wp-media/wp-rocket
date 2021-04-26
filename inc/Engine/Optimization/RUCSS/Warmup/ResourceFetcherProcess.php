<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use \WP_Rocket_WP_Background_Process;

class ResourceFetcherProcess extends WP_Rocket_WP_Background_Process {

	/**
	 * Background process action name.
	 *
	 * @var string
	 */
	protected $action = 'rucss_warmup_resource_fetcher';

	/**
	 * Background process prefix.
	 *
	 * @var string
	 */

	protected $prefix = 'rocket';

	/**
	 * ResourcesQuery instance.
	 *
	 * @var ResourcesQuery
	 */
	private $resources_query;

	/**
	 * APIClient instance.
	 *
	 * @var APIClient
	 */
	private $api_client;

	/**
	 * Flag set if content changed for any resource on the page.
	 *
	 * @var bool
	 */
	private $content_changed = false;

	/**
	 * ResourceFetcherProcess constructor.
	 *
	 * @param ResourcesQuery $resources_query ResourcesQuery instance.
	 * @param APIClient      $api_client APIClient instance.
	 */
	public function __construct( ResourcesQuery $resources_query, APIClient $api_client ) {
		parent::__construct();

		$this->resources_query = $resources_query;
		$this->api_client      = $api_client;
	}


	/**
	 * Do the task for each page resources.
	 *
	 * @param array $resource Resource array consists of url, content, type and media (for css only).
	 *
	 * @return bool False for success, remove item from queue and True for failure so requeue this item.
	 */
	protected function task( $resource ) {
		if ( ! is_array( $resource ) ) {
			return false;
		}

		if ( $this->resources_query->create_or_update( $resource ) ) {
			$this->content_changed = true;
			return ! $this->send_warmup_request( $resource );
		}

		return false;
	}

	/**
	 * Send the warmup request.
	 *
	 * @param array $resource Resource array.
	 *
	 * @return bool
	 */
	protected function send_warmup_request( array $resource ) {
		return $this->api_client->send_warmup_request( $resource );
	}


	/**
	 * The full queue is completed.
	 *
	 * @return void
	 */
	protected function complete() {
		parent::complete();

		if ( ! $this->content_changed ) {
			return;
		}

		do_action( 'rocket_rucss_file_changed' );
	}
}
