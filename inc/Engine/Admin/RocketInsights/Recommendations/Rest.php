<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;

/**
 * REST API Controller for Recommendations.
 *
 * Handles REST API endpoints for fetching recommendation status and data.
 */
class Rest extends WP_REST_Controller {

	const ROUTE_NAMESPACE = 'wp-rocket/v1';
	const ROUTE_BASE      = 'recommendations';

	/**
	 * DataManager instance.
	 *
	 * @var DataManager
	 */
	private $data_manager;

	/**
	 * Constructor.
	 *
	 * @param DataManager $data_manager DataManager instance.
	 */
	public function __construct( DataManager $data_manager ) {
		$this->data_manager = $data_manager;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/status',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_status' ],
					'permission_callback' => [ $this, 'get_status_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Get recommendation status.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_status( $request ) {
		$recommendations = $this->data_manager->get_recommendations();
		$status          = $this->data_manager->get_status();

		$response_data = [
			'status'          => $status,
			'recommendations' => [],
		];

		// If we have valid recommendations data, include it.
		if ( false !== $recommendations && isset( $recommendations['recommendations'] ) ) {
			$response_data['recommendations'] = $recommendations['recommendations'];

			// Include metadata if available.
			if ( isset( $recommendations['metadata'] ) ) {
				$response_data['metadata'] = $recommendations['metadata'];
			}

			// Include error if in failed status.
			if ( 'failed' === $status && isset( $recommendations['error'] ) ) {
				$response_data['error'] = $recommendations['error'];
			}
		}

		return rest_ensure_response( $response_data );
	}

	/**
	 * Check if a given request has access to get recommendation status.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 * @return bool True if the request has access, false otherwise.
	 */
	public function get_status_permissions_check( $request ) {
		return current_user_can( 'rocket_manage_options' );
	}
}
