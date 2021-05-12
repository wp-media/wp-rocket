<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket_WP_Background_Process;
use WP_Rocket\Admin\Options;

class ScannerProcess extends WP_Rocket_WP_Background_Process {
	/**
	 * Background process action name.
	 *
	 * @var string
	 */
	protected $action = 'rucss_warmup_scanner';

	/**
	 * Background process prefix.
	 *
	 * @var string
	 */

	protected $prefix = 'rocket';

	/**
	 * Resource fetcher instance
	 *
	 * @var Resource Fetcher
	 */
	private $resource_fetcher;

	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Instantiate the class
	 *
	 * @param ResourceFetcher $resource_fetcher Resource fetcher instance.
	 * @param Options         $options_api Options API instance.
	 */
	public function __construct( ResourceFetcher $resource_fetcher, Options $options_api ) {
		parent::__construct();

		$this->resource_fetcher = $resource_fetcher;
		$this->options_api      = $options_api;
	}

	/**
	 * Gets the HTML of the page, and send it to the resource fetcher
	 *
	 * @since 3.9
	 *
	 * @param array $item Item to process.
	 *
	 * @return mixed
	 */
	public function task( $item ) {
		if ( ! isset( $item['url'] ) ) {
			return false;
		}

		$response = wp_remote_get(
			add_query_arg( 'nowprocket', '1', $item['url'] ),
			[
				'sslverify' => apply_filters( 'https_local_ssl_verify', false ), // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
			]
		);

		$item['is_error'] = false;

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$item['is_error'] = true;
		}

		$html = wp_remote_retrieve_body( $response );

		if ( empty( $html ) ) {
			$item['is_error'] = true;
		}

		$this->resource_fetcher->data(
			[
				'html'      => $html,
				'prewarmup' => 1,
				'page_url'  => $item['url'],
				'is_error'  => $item['is_error'],
			]
		)->dispatch();

		$option   = $this->options_api->get( 'resources_scanner_scanned', [] );
		$option[] = $item['url'];

		$this->options_api->set( 'resources_scanner_scanned', array_unique( $option ) );

		return false;
	}
}
