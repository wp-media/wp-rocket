<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS\Warmup\Status;

use WP_REST_Request;
use WP_REST_Response;
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
	 * RESTWP constructor.
	 *
	 * @since 3.9
	 *
	 * @param Options_Data $options Instance of options data handler.
	 * @param ResourcesQuery $resources_query Resources Query instance.
	 *
	 */
	public function __construct( Options_Data $options, ResourcesQuery $resources_query ) {
		$this->options       = $options;
		$this->resources_query = $resources_query;
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
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);
	}

	/**
	 * Checks user's permissions. This is a callback registered to REST route's "permission_callback" parameter.
	 *
	 * @since 3.9
	 *
	 * @return bool true if the user has permission; else false.
	 */
	public function check_permissions() {
		return current_user_can( 'rocket_remove_unused_css' );
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
	public function respond_status( WP_REST_Request $request ) : WP_REST_Response {
		$urls = (array) $request->get_param( 'urls' );

		if (
			empty( $urls )
			||
			(bool) $this->options->get( 'remove_unused_css', 0 )
		) {
			return rest_ensure_response(
				$this->return_error( __( 'Remove unused CSS option is disabled or Invalid request!', 'rocket' ) )
			);
		}

		$warmup_total_resources_count = $this->resources_query->query(
			[
				'count' => true,
				'fields' => [
					'prewarmup' => 1,
					'url__in' => [
						'in' => $urls,
					],
				]

			]
		);

		die(var_dump($warmup_total_resources_count));

		if ( empty( $warmup_total_resources_count ) ) {
			return rest_ensure_response(
				$this->return_error( __( 'No resources into the DB!', 'rocket' ) )
			);
		}



	}

	private function return_error( string $message, array $data = [] ) : array {
		return [
			'success' => false,
			'message' => $message,
			'data' => $data,
		];
	}

	private function return_success( string $message, array $data = [] ) : array {
		return [
			'success' => true,
			'message' => $message,
			'data' => $data,
		];
	}

}
