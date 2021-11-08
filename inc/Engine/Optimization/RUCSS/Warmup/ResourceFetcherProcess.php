<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket\Admin\Options;
use WP_Rocket\Dependencies\Minify\CSS as MinifyCSS;
use WP_Rocket\Dependencies\Minify\JS as MinifyJS;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\CSSTrait;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Engine\Optimization\UrlTrait;
use WP_Rocket\Logger\Logger;
use \WP_Rocket_WP_Background_Process;

class ResourceFetcherProcess extends WP_Rocket_WP_Background_Process {

	use UrlTrait, CSSTrait;

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
	 * Assets local cache instance
	 *
	 * @var AssetsLocalCache
	 */
	private $local_cache;

	/**
	 * ResourceFetcherProcess constructor.
	 *
	 * @param ResourcesQuery   $resources_query ResourcesQuery instance.
	 * @param APIClient        $api_client APIClient instance.
	 * @param Options          $options_api Options API instance.
	 * @param AssetsLocalCache $local_cache Local cache instance.
	 */
	public function __construct( ResourcesQuery $resources_query, APIClient $api_client, Options $options_api, AssetsLocalCache $local_cache ) {
		parent::__construct();

		$this->resources_query = $resources_query;
		$this->api_client      = $api_client;
		$this->options_api     = $options_api;
		$this->local_cache     = $local_cache;
	}

	/**
	 * Minify and prepare CSS.
	 *
	 * @param string $path Path of the CSS file.
	 * @param string $contents Contents of the CSS file.
	 *
	 * @return string
	 */
	private function prepare_css_content( string $path, string $contents ) : string {
		$contents = trim( $this->rewrite_paths( $path, $path, $contents ) );
		$minifier = new MinifyCSS( $contents );

		return $minifier->minify();
	}

	/**
	 * Minify and prepare JS.
	 *
	 * @param string $contents Contents of the JS file.
	 *
	 * @return string
	 */
	private function prepare_js_content( string $contents ) : string {
		$minifier = new MinifyJS( $contents );

		return $minifier->minify();
	}

	/**
	 * Get resource contents.
	 *
	 * @param array $resource Resource array.
	 *
	 * @return string
	 */
	private function get_resource_contents( array $resource ) : string {

		$resource_path = ! empty( $resource['path'] ) ? $resource['path'] : '';

		$file_content = ! empty( $resource['external'] ) ? $this->local_cache->get_content( $resource['url'] ) : $this->get_file_content( $resource_path );

		// Minify the content if it's there.
		if ( $file_content ) {
			$file_content = 'js' === $resource['type'] ? $this->prepare_js_content( $file_content ) : $this->prepare_css_content( $resource_path, $file_content );
		}

		if ( ! $file_content ) {
			Logger::error(
				'No file content.',
				[
					'RUCSS warmup process',
					'path' => $resource_path,
				]
			);

			return '*';
		}

		return $file_content;
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

		$resource['content'] = $this->get_resource_contents( $resource );

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
