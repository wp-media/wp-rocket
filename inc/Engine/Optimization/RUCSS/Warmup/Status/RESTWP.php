<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status;

use WP_REST_Request;
use WP_REST_Response;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;

/**
 * Class RESTWP
 *
 * @package WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status
 */
class RESTWP {

	/**
	 * Namespace for REST Route.
	 */
	const API_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Part of route namespace for this inherited class item type.
	 *
	 * @var string $route_namespace to be set with like post, term.
	 */
	const API_ROUTE = 'rucss/warmup/status';

	/**
	 * WP Rocket options instance.
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Resources Query instance.
	 *
	 * @var ResourcesQuery
	 */
	private $resources_query;

	/**
	 * Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * RESTWP constructor.
	 *
	 * @since 3.9
	 *
	 * @param Options_Data   $options Instance of options data handler.
	 * @param ResourcesQuery $resources_query Resources Query instance.
	 * @param Options        $options_api Options API instance.
	 */
	public function __construct( Options_Data $options, ResourcesQuery $resources_query, Options $options_api ) {
		$this->options         = $options;
		$this->resources_query = $resources_query;
		$this->options_api     = $options_api;
	}

	/**
	 * Registers the generate route in the WP REST API
	 *
	 * @since 3.9
	 *
	 * @return void
	 */
	public function register_status_route() {
		register_rest_route(
			self::API_NAMESPACE,
			self::API_ROUTE,
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'respond_status' ],
				'permission_callback' => function() {
					return current_user_can( 'rocket_remove_unused_css' );
				},
			]
		);
	}

	/**
	 * Generates the CPCSS for the requested post ID.
	 *
	 * @since 3.6
	 *
	 * @param WP_REST_Request $request WP REST request response.
	 *
	 * @return WP_REST_Response
	 */
	public function respond_status( WP_REST_Request $request ): WP_REST_Response {
		if (
			(bool) $this->options->get( 'remove_unused_css', 0 )
		) {
			return rest_ensure_response(
				$this->return_error( __( 'Remove unused CSS option is disabled.', 'rocket' ) )
			);
		}

		$resources_scanner_option = $this->options_api->get( 'resources_scanner', [] );
		if ( empty( $resources_scanner_option ) ) {
			return rest_ensure_response(
				$this->return_error( __( 'Pre-Warmup process didn\'t start yet.', 'rocket' ) )
			);
		}

		$output = [
			'scan_status'   => $this->get_scan_status( $resources_scanner_option ),
			'warmup_status' => $this->get_warmup_status(),
		];

		return rest_ensure_response(
			$this->return_success( $output )
		);

	}

	/**
	 * Get array of error response.
	 *
	 * @since 3.9
	 *
	 * @param string $message Error message.
	 * @param array  $data Data to be passed.
	 *
	 * @return array
	 */
	private function return_error( string $message, array $data = [] ): array {
		return [
			'success' => false,
			'message' => $message,
			'data'    => $data,
		];
	}

	/**
	 * Get array of success response.
	 *
	 * @since 3.9
	 *
	 * @param array $data Data to be passed.
	 *
	 * @return array
	 */
	private function return_success( array $data = [] ): array {
		return [
			'success' => true,
			'data'    => $data,
		];
	}

	/**
	 * Get scan status array based on the passed option array.
	 *
	 * @param array $resources_scanner_option Option array that has scanning details.
	 *
	 * @return array
	 */
	private function get_scan_status( array $resources_scanner_option ) : array {
		$status = [
			'total_pages' => count( $resources_scanner_option ),
			'scanned'     => 0,
			'fetched'     => 0,
		];

		foreach ( $resources_scanner_option as $item ) {
			if ( ! empty( $item['is_scanned'] ) ) {
				$status['scanned']++;
			}

			if ( ! empty( $item['is_fetched'] ) ) {
				$status['fetched']++;
			}
		}

		return $status;
	}

	/**
	 * Get warmup status from the DB.
	 *
	 * @return array
	 */
	private function get_warmup_status() : array {
		$status = [
			'total'               => $this->resources_query->get_prewarmup_total_count(),
			'warmed_count'        => $this->resources_query->get_prewarmup_warmed_count(),
			'notwarmed_resources' => [],
		];

		if ( $status['warmed_count'] < $status['total'] ) {
			$status['notwarmed_resources'] = array_values( $this->resources_query->get_prewarmup_notwarmed_urls() );
		}

		return $status;
	}

}
