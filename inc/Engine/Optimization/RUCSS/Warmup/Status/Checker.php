<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Logger\Logger;

class Checker extends AbstractAPIClient {
	/**
	 * Options API instance
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Resources Query instance
	 *
	 * @var ResourcesQuery
	 */
	private $resources_query;

	/**
	 * Status checker endpoint
	 *
	 * @var string
	 */
	protected $request_path = 'resources/statusbyurl';

	/**
	 * Instantiate the class
	 *
	 * @param Options        $options_api Options API instance.
	 * @param Options_Data   $options Options instance.
	 * @param ResourcesQuery $resources_query Resources Query instance.
	 */
	public function __construct( Options $options_api, Options_Data $options, ResourcesQuery $resources_query ) {
		parent::__construct( $options );

		$this->options_api     = $options_api;
		$this->resources_query = $resources_query;
	}


	/**
	 * Update Warmup process on completion.
	 *
	 * @return void
	 */
	private function set_warmup_status_completed() {
		$this->set_warmup_status_finish_time();
		$this->set_warmup_force_optimization();

		/**
		 * Fires when the Pre-warmup process is completed.
		 *
		 * @since 3.9.0.1
		 */
		do_action( 'rocket_prewarmup_finished' );
	}

	/**
	 * Automatically stop warmup process after 1 hour in case the process did not finished.
	 *
	 * @return void
	 */
	public function auto_stop_warmup_after_1hour() {
		if ( $this->is_warmup_finished() ) {
			return;
		}

		$prewarmup_stats = $this->options_api->get( 'prewarmup_stats', [] );

		if ( empty( $prewarmup_stats['scan_start_time'] ) ) {
			return;
		}

		if ( time() > strtotime( '+1 hour', (int) $prewarmup_stats['scan_start_time'] ) ) {
			$this->set_warmup_status_completed();
		}
	}

	/**
	 * Activate RUCSS and set warmup finish time when warmup is completed.
	 *
	 * @return void
	 */
	public function activate_optimization_on_warmup_completion() {
		// Bailout in case scanner fetching is not finished.
		if ( $this->is_warmup_finished() || ! $this->is_scanner_fetching_finished() ) {
			return;
		}

		$items = $this->resources_query->get_waiting_prewarmup_items();

		if ( ! empty( $items ) ) {
			return;
		}

		$this->set_warmup_status_completed();

		rocket_clean_domain();
	}

	/**
	 * Check if Warmup is finished.
	 * Warmup is finished wtih allow_optimization is true.
	 *
	 * @return boolean
	 */
	public function is_warmup_finished(): bool {
		$prewarmup_stats = $this->options_api->get( 'prewarmup_stats', [] );
		return ! empty( $prewarmup_stats['allow_optimization'] );
	}

	/**
	 * Check if Scanner Fetching step is completed.
	 * Fetching is completed if the scan_start_time is set and fetch_finish_time is set.
	 *
	 * @return boolean
	 */
	private function is_scanner_fetching_finished(): bool {
		$prewarmup_stats = $this->options_api->get( 'prewarmup_stats', [] );

		return ! empty( $prewarmup_stats['fetch_finish_time'] ) && $this->is_scanner_scan_finished();
	}

	/**
	 * Check if Scanner Scan step is completed.
	 * Scan is finished if the scan_start_time is set.
	 *
	 * @return boolean
	 */
	private function is_scanner_scan_finished(): bool {
		$prewarmup_stats = $this->options_api->get( 'prewarmup_stats', [] );

		return ! empty( $prewarmup_stats['scan_start_time'] );
	}

	/**
	 * Checks warmup status for resources in the prewarmup process.
	 *
	 * @return void
	 */
	public function update_warmup_status_while_has_items() {
		if ( $this->is_warmup_finished() || ! $this->is_scanner_fetching_finished() ) {
			return;
		}

		$items = $this->resources_query->get_waiting_prewarmup_items();

		if ( empty( $items ) ) {
			return;
		}

		$request = $this->handle_post(
			[
				'body' => [
					'urls' => $this->prepare_resources_array( $items ),
				],
			]
		);

		if ( ! $request ) {
			return;
		}

		$this->update_from_response();
	}

	/**
	 * Prepares the array to send to the endpoint
	 *
	 * @since 3.9
	 *
	 * @param array $items Array of items from the database.
	 *
	 * @return array
	 */
	private function prepare_resources_array( array $items ): array {
		$resources = [
			'css' => [],
			'js'  => [],
		];

		foreach ( $items as $item ) {
			if ( ! in_array( $item->type, [ 'css', 'js' ], true ) ) {
				continue;
			}

			$resources[ $item->type ][] = $item->url;
		}

		return $resources;
	}

	/**
	 * Updates the database entries based on the API response
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	private function update_from_response() {
		$response = json_decode( $this->response_body );
		if ( empty( $response->contents ) ) {
			return;
		}

		foreach ( $response->contents as $url => $status ) {
			if ( false === $status ) {
				continue;
			}

			$this->resources_query->update_warmup_status( $url );
		}
	}

	/**
	 * Set warmup Status process finish time.
	 */
	private function set_warmup_status_finish_time() {
		$prewarmup_stats = $this->options_api->get( 'prewarmup_stats', [] );

		if ( ! empty( $prewarmup_stats['warmup_status_finish_time'] ) ) {
			return;
		}

		$prewarmup_stats['warmup_status_finish_time'] = time();
		$this->options_api->set( 'prewarmup_stats', $prewarmup_stats );
	}

	/**
	 * Set warmup force optimization.
	 */
	private function set_warmup_force_optimization() {
		$prewarmup_stats                       = $this->options_api->get( 'prewarmup_stats', [] );
		$prewarmup_stats['allow_optimization'] = true;
		$this->options_api->set( 'prewarmup_stats', $prewarmup_stats );
	}
}
