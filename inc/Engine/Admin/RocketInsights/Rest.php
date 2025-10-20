<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Server;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\{
	Render,
	PageHandlerTrait,
	GlobalScore,
	Jobs\Manager,
	Context\PerformanceMonitoringContext as Context,
	Database\Queries\PerformanceMonitoring as Query,
	Managers\Plan
};
use WP_Rocket\Engine\Common\Utils;

class REST extends WP_REST_Controller {
	use PageHandlerTrait;

	const ROUTE_NAMESPACE = 'wp-rocket/v1';
	const ROUTE_BASE      = 'rocket-insights';

	/**
	 * Query object.
	 *
	 * @var Query
	 */
	private $query;

	/**
	 * Manager instance.
	 *
	 * @var Manager
	 */
	private $manager;

	/**
	 * Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * GlobalScore instance.
	 *
	 * @var GlobalScore
	 */
	private $global_score;

	/**
	 * Render instance.
	 *
	 * @var Render
	 */
	private $render;

	/**
	 * Plan instance.
	 *
	 * @var Plan
	 */
	private $plan;

	/**
	 * Constructor.
	 *
	 * @param Query       $query Query instance.
	 * @param Manager     $manager Manager instance.
	 * @param Context     $context Context instance.
	 * @param GlobalScore $global_score GlobalScore instance.
	 * @param Render      $render Render instance.
	 * @param Plan        $plan Plan instance.
	 */
	public function __construct( Query $query, Manager $manager, Context $context, GlobalScore $global_score, Render $render, Plan $plan ) {
		$this->query        = $query;
		$this->manager      = $manager;
		$this->context      = $context;
		$this->global_score = $global_score;
		$this->render       = $render;
		$this->plan         = $plan;
	}

	/**
	 *  Registers the routes for the objects of the controller.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/pages',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_items' ],
					'permission_callback' => [ $this, 'get_items_permissions_check' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
					'args'                => [
						'page_url' => [
							'required' => true,
							'validation_callback' => function ( $param ) {
								$url = untrailingslashit( trim( wp_unslash( $param ) ) );
								$url = rocket_add_url_protocol( $url );

								return wp_http_validate_url( $url );
							},
							'sanitize_callback' => function ( $param ) {
								$url = untrailingslashit( trim( wp_unslash( $param ) ) );

								return rocket_add_url_protocol( $url );
							},
						],
					],
				],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/pages/progress',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_progress' ],
				'permission_callback' => [ $this, 'get_progress_permissions_check' ],
				'args'                => [
					'ids' => [
						'required' => true,
						'validate_callback' => function ( $param ) {
							if ( ! is_array( $param ) ) {
								return false;
							}

							foreach ( $param as $id ) {
								if ( ! is_numeric( $id ) ) {
									return false;
								}
							}

							return true;
						},
						'sanitize_callback' => function ( $param ) {
							$ids= array_map( 'intval', $param );
							// Remove anything that is not a valid integer > 0.
							$ids = array_filter( $ids );

							// Keep index clean.
							$ids = array_values( $ids );

							return $ids;
						},
					],
				],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/pages/(?P<id>\d+)',
			[
				[
					'methods'            => WP_REST_Server::DELETABLE,
					'callback'           => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permissions_check' ],
					'args'               => [
						'id' => [
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => function ( $param ) {
								return intval( $param );
							},
						],
					],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
					'args'               => [
						'id' => [
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => function ( $param ) {
								return intval( $param );
							},
						],
					],
				]
			]
		);
	}

	/**
	 * Creates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function create_item( $request ) {
		// Check if adding a page is allowed based on URL limits.
		if ( ! $this->context->is_adding_page_allowed() ) {
			$data = [
					'error'          => true,
					'message'        => __( 'Maximum number of URLs reached for your license.', 'rocket' ),
					'remaining_urls' => 0,
					'can_add_pages'  => false,
				];

			return rest_ensure_response( $data );
		}

		$payload = $this->get_url_validation_payload( $request['page_url'] );

		if ( $payload['error'] ) {
			return rest_ensure_response( $payload );
		}

		$url = $payload['processed_url'];

		if ( Utils::is_home( $url ) ) {
			$page_title = __( 'Homepage', 'rocket' );
		} else {
			$page_title = $this->get_page_title( $payload['message'] );
		}

		$row_id = $this->manager->add_to_the_queue(
			$url,
			true,
			[
				'title' => $page_title,
			]
			);

		if ( empty( $row_id ) ) {
			$data = [
				'error'   => true,
				'message' => esc_html__( 'Not valid inputs', 'rocket' ),
			];

			return rest_ensure_response( $data );
		}

		$urls_count   = $this->query->get_total_count();
		$current_plan = $this->plan->get_current_plan();

		/**
		 * Fires when a performance monitoring job is added via AJAX.
		 *
		 * @since 3.20
		 *
		 * @param string $url        The URL that was added for monitoring.
		 * @param string $plan       Plan name.
		 * @param int    $urls_count The current number of URLs being monitored.
		 */
		do_action( 'rocket_pm_job_added', $url, $current_plan, $urls_count );

		$row_data = $this->query->get_row_by_id( (int) $row_id );

		// Remove message from the response payload.
		unset( $payload['message'] );

		$payload['id']                = $row_id;
		$payload['html']              = $this->render->get_performance_monitoring_list_row( $row_data );
		$payload['global_score_data'] = $this->get_global_score_payload();
		$payload['remaining_urls']    = $this->get_remaining_url_count();
		$payload['has_credit']        = $this->plan->has_credit();
		$payload['can_add_pages']     = $this->context->is_adding_page_allowed();

		// Add disabled button html data to payload.
		if ( 0 === $this->get_remaining_url_count() ) {
			$data                  = $payload['global_score_data']['data'];
			$data['reach_max_url'] = true;

			$payload['global_score_data']['disabled_btn_html'] = [
				'global_score_widget' => $this->render->get_add_page_btn( 'global-score-widget', $data ),
				'rocket_insights'     => $this->render->get_add_page_btn( 'rocket-insights', $data ),
			];
		}

		return rest_ensure_response( $payload );
	}

	/**
	 * Checks if a given request has access to create items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error
	 */
	public function create_item_permissions_check( $request ) {
		if ( ! $this->context->is_allowed() ) {
			return new WP_Error( 'rest_forbidden', __( 'You are not allowed to create this item.', 'rocket' ), [ 'status' => 403 ] );
		}

		return true;
	}

	/**
	 * Deletes one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function delete_item( $request ) {
		if ( empty( $request['id'] ) ) {
			$error = new WP_Error( 'rest_invalid_param', __( 'Invalid item ID.', 'rocket' ), [ 'status' => 400 ] );

			return rest_ensure_response( $error );
		}

		$result = $this->query->delete_item( $request['id'] );

		/**
		 * Fires when a performance monitoring job is deleted.
		 *
		 * @since 3.20
		 *
		 * @param int $id The ID of the deleted performance monitoring job.
		 */
		do_action( 'rocket_pm_job_deleted', $request['id'] );

		return rest_ensure_response( $result );
	}

	/**
	 * Checks if a given request has access to delete a specific item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error
	 */
	public function delete_item_permissions_check( $request ) {
		if ( ! $this->context->is_allowed() ) {
			return new WP_Error( 'rest_forbidden', __( 'You are not allowed to delete this item.', 'rocket' ), [ 'status' => 403 ] );
		}

		return true;
	}

	/**
	 * Updates one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function update_item( $request ) {
		if ( empty( $request['id'] ) ) {
			$error = new WP_Error( 'rest_invalid_param', __( 'No ID was provided.', 'rocket' ), [ 'status' => 400 ] );

			return rest_ensure_response( $error );
		}

		$row = $this->query->get_row_by_id( $request['id'] );

		if ( ! $row ) {
			$error = new WP_Error( 'rest_not_found', __( 'Item not found.', 'rocket' ), [ 'status' => 404 ] );

			return rest_ensure_response( $error );
		}

		$this->manager->add_to_the_queue(
			$row->url, // @phpstan-ignore-line
			true,
			[
				'data'       => [
					'is_retest' => true,
				],
				'score'      => '',
				'report_url' => '',
				'is_blurred' => 0,
			]
		);

		/**
		 * Fires when a performance monitoring job is reset/retested.
		 *
		 * @since 3.20
		 *
		 * @param int    $id The database row ID of the reset job.
		 */
		do_action( 'rocket_pm_job_retest', $request['id'] );

		$row = $this->query->get_row_by_id( $request['id'] );

		$data = [
			'id'                => $request['id'],
			'html'              => $this->render->get_performance_monitoring_list_row( $row ),
			'global_score_data' => $this->get_global_score_payload(),
			'remaining_urls'    => $this->get_remaining_url_count(),
			'has_credit'        => $this->plan->has_credit(),
			'can_add_pages'     => $this->context->is_adding_page_allowed(),
		];

		return rest_ensure_response( $data );
	}

	/**
	 * Checks if a given request has access to update a specific item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! current_user_can( 'rocket_manage_options' ) ) {
			return new WP_Error( 'rest_forbidden', __( 'You are not allowed to update this item.', 'rocket' ), [ 'status' => 403 ] );
		}

		return true;
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$items = $this->query->get_items();

		if ( empty( $items ) ) {
			return rest_ensure_response( $items );
		}

		$data = [];

		return rest_ensure_response( $items );
	}

	/**
	 * Checks if a given request has access to get items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error
	 */
	public function get_items_permissions_check( $request ) {
		if ( ! $this->context->is_allowed() ) {
			return new WP_Error( 'rest_forbidden', __( 'You are not allowed to access items.', 'rocket' ), [ 'status' => 403 ] );
		}

		return true;
	}

	/**
	 * Retrieves the progress of one or more items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_progress( $request ) {
		$payload = [];

		if ( empty( $request['ids'] ) ) {
			$payload['results'] = 'ids empty';

			return rest_ensure_response( $payload );
		}

		$query_params = [
			'id__in' => $request['ids'],
		];

		$results = $this->query->query( $query_params );

		// Result is empty.
		if ( empty( $results ) ) {
			$payload['results'] = 'No rows found in DB for ids: ' . implode( ',', $request['ids'] );

			return rest_ensure_response( $payload );
		}

		foreach ( $results as $result ) {
			$result->html = $this->render->get_performance_monitoring_list_row( $result );
		}

		$payload['results']           = $results;
		$payload['global_score_data'] = $this->get_global_score_payload();
		$payload['has_credit']        = $this->plan->has_credit();
		$payload['can_add_pages']     = $this->context->is_adding_page_allowed();

		return rest_ensure_response( $payload );
	}

	/**
	 * Checks if a given request has access to get progress.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error
	 */
	public function get_progress_permissions_check( $request ) {
		if ( ! $this->context->is_allowed() ) {
			return new WP_Error( 'rest_forbidden', __( 'You are not allowed to access items.', 'rocket' ), [ 'status' => 403 ] );
		}

		return true;
	}

	/**
	 * Validates a given URL for performance monitoring eligibility.
	 *
	 * @param string $url The URL to validate.
	 *
	 * @return array {
	 *     @type bool   $error        Whether an error occurred during validation.
	 *     @type string $message      The error message, or an empty string if no error.
	 *     @type string $processed_url The URL with protocol added if validation passes.
	 * }
	 */
	protected function get_url_validation_payload( string $url ): array {
		$payload = [
			'error'         => false,
			'message'       => '',
			'processed_url' => '',
		];

		if ( 'local' === wp_get_environment_type() ) {
			$payload['error']   = true;
			$payload['message'] = 'Performance monitoring is disabled for local environment';

			return $payload;
		}

		// Validate that performance monitoring is not disabled.
		if ( ! $this->context->is_allowed() ) {
			$payload['error']   = true;
			$payload['message'] = 'Performance monitoring is disabled.';

			return $payload;
		}
		// Validate that url is not empty.
		if ( '' === $url ) {
			$payload['error']   = true;
			$payload['message'] = 'No url provided.';

			return $payload;
		}

		// Check if URL has protocol, add if needed.
		$url                      = rocket_add_url_protocol( $url );
		$payload['processed_url'] = $url;

		$response = $this->get_page_content( $url );

		if ( ! $response ) {
			$payload['error']   = true;
			$payload['message'] = 'Url does not resolve to a valid page.';

			return $payload;
		}

		// check if url is not from admin.
		if ( strpos( $url, admin_url() ) === 0 ) {
			$payload['error']   = true;
			$payload['message'] = 'Url is an admin page.';

			return $payload;
		}

		// Check if url has not been submited.
		if ( false !== $this->manager->get_single_job( $url, true ) ) {
			$payload['error']   = true;
			$payload['message'] = 'Page url performance is already been monitored.';

			return $payload;
		}

		// Fetch url body and send to payload.
		$payload['message'] = $response;

		return $payload;
	}

	/**
	 * Retrieves the global performance score payload for AJAX responses.
	 *
	 * Gets the global score data, determines the status color, and generates the HTML
	 * for the global score widget.
	 *
	 * @return array {
	 *     @type array  $data Global score data including score, pages_num, status, and status-color.
	 *     @type string $html Rendered HTML for the global score widget.
	 * }
	 */
	private function get_global_score_payload() {
		$payload                   = $this->global_score->get_global_score_data();
		$payload['status-color']   = $this->render->get_score_color_status( (int) $payload['score'] );
		$payload['remaining_urls'] = $this->get_remaining_url_count();

		return [
			'data'     => $payload,
			'html'     => $this->render->get_global_score_widget_content( $payload ),
			'row_html' => $this->render->get_global_score_row( $payload ),
		];
	}

	/**
	 * Get the remaining number of URLs that can be added based on user's plan limit.
	 *
	 * @return int Number of URLs that can still be added.
	 */
	private function get_remaining_url_count(): int {
		return max(
			0,
			$this->plan->max_urls() - (int) $this->query->
			get_total_count()
		);
	}
}
