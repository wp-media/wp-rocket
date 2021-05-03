<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Admin\Options;
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
	 * Current page url that has the current resource.
	 *
	 * @var string
	 */
	private $page_url = '';

	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * ResourceFetcherProcess constructor.
	 *
	 * @param ResourcesQuery $resources_query ResourcesQuery instance.
	 * @param APIClient      $api_client APIClient instance.
	 * @param Options        $options_api Options API instance.
	 */
	public function __construct( ResourcesQuery $resources_query, APIClient $api_client, Options $options_api ) {
		parent::__construct();

		$this->resources_query = $resources_query;
		$this->api_client      = $api_client;
		$this->options_api     = $options_api;
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

		$this->page_url = $resource['page_url'] ?? '';

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

	/**
	 * Batch completed callback.
	 */
	protected function complete_batch() {
		if ( empty( $this->page_url ) ) {
			return;
		}

		$option          = $this->options_api->get( 'resources_scanner', [] );
		$fetched_counter = 0;
		foreach ( $option as $key => $item ) {
			if ( isset( $item['is_fetched'] ) && $item['is_fetched'] ) {
				$fetched_counter++;
			}
			if ( $this->page_url !== $item['url'] ) {
				continue;
			}

			$option[ $key ]['is_fetched'] = true;
			$fetched_counter++;
		}

		$this->options_api->set( 'resources_scanner', $option );

		if ( count( $option ) === $fetched_counter ) {
			// Fetching resources is finished.
			$prewarmup_stats                      = $this->options_api->get( 'prewarmup_stats', [] );
			$prewarmup_stats['fetch_finish_time'] = time();
			$this->options_api->set( 'prewarmup_stats', $prewarmup_stats );
		}
	}
}
