<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status;

use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\AbstractAPIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;

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
	 * Checks warmup status for resources in the prewarmup process
	 *
	 * @return void
	 */
	public function check_warmup_status() {
		$start_time = $this->options_api->get( 'scanner_start_time', false );

		if ( false === $start_time ) {
			return;
		}

		if ( time() > strtotime( '+1 hour', (int) $start_time ) ) {
			/**
			 * Fires this action when the prewarmup lifespan is expired
			 *
			 * @since 3.9
			 */
			do_action( 'rocket_rucss_prewarmup_error' );

			$this->options_api->delete( 'scanner_start_time' );
			rocket_clean_domain();

			return;
		}

		$items = $this->resources_query->get_waiting_prewarmup_items();

		if ( empty( $items ) ) {
			/**
			 * Fires this action when the prewarmup is complete
			 *
			 * @since 3.9
			 */
			do_action( 'rocket_rucss_prewarmup_success' );

			$this->options_api->delete( 'scanner_start_time' );
			rocket_clean_domain();

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
	 * Prepares the success transient to be used for the RUCSS prewarmup notice
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function prepare_success_notice() {
		$message = '<p>' . __( 'WP Rocket: Remove Unused CSS warmup is complete!', 'rocket' ) . '</p>';

		$notice_data = [
			'message' => $message,
		];

		set_transient( 'rocket_rucss_prewarmup_notice', $notice_data, HOUR_IN_SECONDS );
	}

	/**
	 * Prepares the error transient to be used for the RUCSS prewarmup notice
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function prepare_error_notice() {
		$items = $this->resources_query->get_waiting_prewarmup_items();

		$urls = wp_list_pluck( $items, 'url' );

		$message = '<p>' . __( 'WP Rocket: Remove Unused CSS warmup was not fully completed. You can find the resources that were not warmed-up below:', 'rocket' ) . '</p>';

		if ( ! empty( $urls ) ) {
			$message .= '<ul>';

			foreach ( $urls as $url ) {
				$message .= '<li>' . $url . '</li>';
			}

			$message .= '</ul>';
		}

		$notice_data = [
			'status'  => 'warning',
			'message' => $message,
		];

		set_transient( 'rocket_rucss_prewarmup_notice', $notice_data, HOUR_IN_SECONDS );
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

		if ( empty( $response->data ) ) {
			return;
		}

		foreach ( $response->data as $url => $status ) {
			if ( false === $status ) {
				continue;
			}

			$this->resources_query->update_warmup_status( $url );
		}
	}
}
