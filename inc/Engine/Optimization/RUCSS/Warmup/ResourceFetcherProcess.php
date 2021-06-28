<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Logger\Logger;
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
	 * Get url file contents.
	 *
	 * @param string $url File url.
	 * @param string $type File type (css,js).
	 *
	 * @return array
	 */
	private function get_url_contents( $url, string $type = 'css' ) : array {
		$external_url = $this->is_external_file( $url );

		$file_path = $external_url ? $this->local_cache->get_filepath( $url ) : $this->get_file_path( $url );

		if ( empty( $file_path ) ) {
			Logger::error(
				'Couldn’t get the file path from the URL.',
				[
					'RUCSS warmup process',
					'url' => $url,
				]
			);

			return [ md5( uniqid() ), '*' ];
		}

		$file_content = $external_url ? $this->local_cache->get_content( $url ) : $this->get_file_content( $file_path );

		// Minify the content if it's there.
		if ( $file_content ) {
			$file_content = 'js' === $type ? $this->prepare_js_content( $file_content ) : $this->prepare_css_content( $file_path, $file_content );
		}

		if ( ! $file_content ) {
			Logger::error(
				'No file content.',
				[
					'RUCSS warmup process',
					'path' => $file_path,
				]
			);

			return [ md5( uniqid() ), '*' ];
		}

		return [ $file_path, $file_content ];
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

		$page_url      = $resource['page_url'] ?? '';
		$all_pages     = $this->options_api->get( 'resources_scanner', [] );
		$fetched_pages = $this->options_api->get( 'resources_scanner_fetched', [] );

		if ( ! empty( $page_url ) ) {
			$fetched_pages[ $page_url ] = [
				'url'      => $page_url,
				'is_error' => false,
			];
		}

		$this->options_api->set( 'resources_scanner_fetched', $fetched_pages );

		if ( count( $all_pages ) === count( $fetched_pages ) ) {
			// Fetching resources is finished.
			$prewarmup_stats                      = $this->options_api->get( 'prewarmup_stats', [] );
			$prewarmup_stats['fetch_finish_time'] = time();
			$this->options_api->set( 'prewarmup_stats', $prewarmup_stats );
		}

		// Check if no resources are sent for the page_url.
		// This usually happens in case of page error.
		if ( empty( $resource['url'] ) ) {
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

		/**
		 * Fires when the resource fetcher process is complete
		 *
		 * @since 3.9
		 */
		do_action( 'rocket_rucss_file_changed' );
	}
}
