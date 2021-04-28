<?php

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup;

use WP_Rocket_WP_Background_Process;

class ScannerProcess extends WP_Rocket_WP_Background_Process {
	/**
	 * Resource fetcher instance
	 *
	 * @var Resource Fetcher
	 */
	private $resource_fetcher;

	/**
	 * Instantiate the class
	 *
	 * @param ResourceFetcher $resource_fetcher Resource fetcher instance.
	 */
	public function __construct( ResourceFetcher $resource_fetcher ) {
		parent::__construct();

		$this->resource_fetcher = $resource_fetcher;
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

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return false;
		}

		$html = wp_remote_retrieve_body( $response );

		if ( empty( $html ) ) {
			return false;
		}

		$this->resource_fetcher->data(
			[
				'html' => $html,
			]
		)->dispatch();

		$item['is_scanned'] = true;
		$option             = get_option( 'wp_rocket_resources_scanner', [] );
		$option[]           = $item;

		update_option( 'wp_rocket_resources_scanner', $option );

		return false;
	}
}
