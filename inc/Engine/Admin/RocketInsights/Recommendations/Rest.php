<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights\Recommendations;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;
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
	 * Registers the routes for the objects of the controller.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE,
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_recommendations' ],
					'permission_callback' => [ $this, 'get_recommendations_permissions_check' ],
				],
			]
		);
	}

	/**
	 * Get recommendations.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_recommendations() {
		/**
		 * Filters the Rest API recommendations response.
		 *
		 * @return array Modified Rest API response.
		 */
		$response_data = wpm_apply_filters_typed( 'array', 'rocket_insights_recommendations_rest_response', [] );
		return rest_ensure_response( $response_data );
	}

	/**
	 * Check if a given request has access to get recommendation status.
	 *
	 * @return bool True if the request has access, false otherwise.
	 */
	public function get_recommendations_permissions_check() {
		return current_user_can( 'rocket_manage_options' );
	}
}
