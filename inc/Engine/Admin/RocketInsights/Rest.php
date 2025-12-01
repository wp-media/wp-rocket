<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Admin\RocketInsights;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Response;
use WP_REST_Request;
use WP_REST_Server;
use WP_Rocket\Engine\Admin\RocketInsights\{
	Jobs\Manager,
	Context\Context,
	Database\Queries\RocketInsights as Query,
	Managers\Plan
};
use WP_Rocket\Engine\Common\{
	JobManager\JobProcessor,
	JobManager\Queue\Queue,
	Utils
};
use WP_Rocket\Logger\Logger;

class Rest extends WP_REST_Controller {
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
	 * JobProcessor instance.
	 *
	 * @var JobProcessor
	 */
	private $job_processor;

	/**
	 * Queue instance for managing jobs.
	 *
	 * @var Queue
	 */
	private $queue;

	/**
	 * Constructor.
	 *
	 * @param Query        $query Query instance.
	 * @param Manager      $manager Manager instance.
	 * @param Context      $context Context instance.
	 * @param GlobalScore  $global_score GlobalScore instance.
	 * @param Render       $render Render instance.
	 * @param Plan         $plan Plan instance.
	 * @param JobProcessor $job_processor JobProcessor instance.
	 * @param Queue        $queue Queue instance.
	 */
	public function __construct(
		Query $query,
		Manager $manager,
		Context $context,
		GlobalScore $global_score,
		Render $render,
		Plan $plan,
		JobProcessor $job_processor,
		Queue $queue
	) {
		$this->query         = $query;
		$this->manager       = $manager;
		$this->context       = $context;
		$this->global_score  = $global_score;
		$this->render        = $render;
		$this->plan          = $plan;
		$this->job_processor = $job_processor;
		$this->queue         = $queue;
	}

	/**
	 *  Registers the routes for the objects of the controller.
	 *
	 * @return void
	 */
	public function register_routes(): void {
		// Route: GET /pages and GET /pages?url={url}.
		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/pages',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => function ( $request ) {
						// If 'url' query param is present and not empty, return single item, otherwise return all items.
						$url = $request->get_param( 'url' );

						if ( ! empty( $url ) ) {
							return $this->get_item( $request );
						}
						return $this->get_items( $request );
					},
					'permission_callback' => function ( $request ) {
						// If 'url' query param is present and not empty, check get_item permissions, otherwise get_items permissions.
						$url = $request->get_param( 'url' );
						if ( ! empty( $url ) ) {
							return $this->get_item_permissions_check( $request );
						}
						return $this->get_items_permissions_check( $request );
					},
					'args'                => [
						'url'     => [
							'required'          => false,
							'validate_callback' => function ( $param ) {
								// Allow empty for optional parameter.
								if ( empty( $param ) ) {
									return true;
								}

								$url = untrailingslashit( trim( $param ) );
								$url = rocket_add_url_protocol( $url );

								return wp_http_validate_url( $url );
							},
							'sanitize_callback' => function ( $param ) {
								// Don't process empty parameter.
								if ( empty( $param ) ) {
									return $param;
								}

								$url = untrailingslashit( trim( $param ) );

								return rocket_add_url_protocol( $url );
							},
						],
						'post_id' => [
							'required'          => false,
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => function ( $param ) {
								// Type cast post id to int.
								return (int) $param;
							},
						],
					],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'create_item' ],
					'permission_callback' => [ $this, 'create_item_permissions_check' ],
					'args'                => [
						'page_url' => [
							'required'          => true,
							'validate_callback' => function ( $param ) {
								if ( empty( $param ) ) {
									return false;
								}

								$url = untrailingslashit( trim( $param ) );
								$url = rocket_add_url_protocol( $url );

								return wp_http_validate_url( $url );
							},
							'sanitize_callback' => function ( $param ) {
								$url = untrailingslashit( trim( $param ) );

								return rocket_add_url_protocol( $url );
							},
						],
						'source'   => [
							'required'          => true,
							'validate_callback' => function ( $param ) {
								$allowed_sources = [ 'dashboard', 'post type listing', 'add-on page', 'auto-added homepage', 'performance monitoring', 're-test post type listing', 're-test add-on page' ];
								return in_array( $param, $allowed_sources, true );
							},
							'sanitize_callback' => function ( $param ) {
								return sanitize_text_field( $param );
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
						'required'          => true,
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
							$ids = array_map( 'intval', $param );
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
					'methods'             => WP_REST_Server::DELETABLE,
					'callback'            => [ $this, 'delete_item' ],
					'permission_callback' => [ $this, 'delete_item_permissions_check' ],
					'args'                => [
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
					'methods'             => WP_REST_Server::EDITABLE,
					'callback'            => [ $this, 'update_item' ],
					'permission_callback' => [ $this, 'update_item_permissions_check' ],
					'args'                => [
						'id'     => [
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return is_numeric( $param );
							},
							'sanitize_callback' => function ( $param ) {
								return intval( $param );
							},
						],
						'source' => [
							'required'          => true,
							'validate_callback' => function ( $param ) {
								$allowed_sources = [ 'dashboard', 'post type listing', 'add-on page', 'auto-added homepage', 'performance monitoring', 're-test post type listing', 're-test add-on page' ];
								return in_array( $param, $allowed_sources, true );
							},
							'sanitize_callback' => function ( $param ) {
								return sanitize_text_field( $param );
							},
						],
					],
				],
			]
		);
	}

	/**
	 * Retrieves one item from the collection.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_item( $request ) {
		$html = $this->render->get_rocket_insights_column( $request['url'], $request['post_id'] );

		$payload = [
			'success' => true,
			'html'    => $html,
		];

		return rest_ensure_response( $payload );
	}

	/**
	 * Checks if a given request has access to get a specific item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error
	 */
	public function get_item_permissions_check( $request ) {

		if ( ! $this->context->is_allowed() ) {
			return new WP_Error( 'rest_forbidden', __( 'You are not allowed to access this item.', 'rocket' ), [ 'status' => 403 ] );
		}

		return true;
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
			$error = new WP_Error(
				'rest_forbidden',
				$this->get_page_limit_error_message(),
				[
					'status'         => 403,
					'remaining_urls' => 0,
					'can_add_pages'  => false,
				]
			);

			return rest_ensure_response( $error );
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

		$source = $request->get_param( 'source' );

		$additional_details = [
			'title' => $page_title,
			'data'  => [
				'source' => $source,
			],
		];

		// Handle synchronous submission using shared method.
		$row_id = $this->handle_sync_submission( $url, true, $additional_details );

		if ( empty( $row_id ) ) {
			$error = new WP_Error(
				'rest_invalid_input',
				esc_html__( 'Not valid inputs', 'rocket' ),
				[ 'status' => 500 ]
			);

			return rest_ensure_response( $error );
		}

		// Check URL limit again after insertion to handle race conditions.
		// If the limit is exceeded, remove the newly added URL and return an error.
		if ( $this->query->get_total_count() > $this->plan->max_urls() ) {
			// Delete the newly added URL.
			$this->query->delete_item( $row_id );

			$error = new WP_Error(
				'rest_forbidden',
				__( 'Maximum number of URLs reached for your license.', 'rocket' ),
				[
					'status'         => 403,
					'remaining_urls' => 0,
					'can_add_pages'  => false,
				]
			);

			return rest_ensure_response( $error );
		}

		$urls_count   = $this->query->get_total_count();
		$current_plan = $this->plan->get_current_plan();

		/**
		 * Fires when a performance monitoring job is added.
		 *
		 * @since 3.20
		 *
		 * @param string $url        The URL that was added for monitoring.
		 * @param string $plan       Plan name.
		 * @param int    $urls_count The current number of URLs being monitored.
		 * @param string $source     The source of the request.
		 */
		do_action( 'rocket_rocket_insights_job_added', $url, $current_plan, $urls_count, $source );

		$row_data = $this->query->get_row_by_id( (int) $row_id );

		// Remove message from the response payload.
		unset( $payload['message'] );

		$payload['success']           = true;
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
		do_action( 'rocket_rocket_insights_job_deleted', $request['id'] );

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

		// Check if adding a page is allowed based on URL limits.
		if ( ! $this->plan->has_credit() ) {
			$error = new WP_Error(
				'rest_forbidden',
				esc_html__( 'Upgrade your plan to get access to re-test performance or run new tests', 'rocket' ),
				[
					'status'         => 403,
					'remaining_urls' => 0,
					'can_add_pages'  => false,
				]
			);

			return rest_ensure_response( $error );
		}

		$source = $request->get_param( 'source' );

		$additional_details = [
			'data'       => [
				'is_retest' => true,
				'source'    => $source,
			],
			'score'      => '',
			'report_url' => '',
			'is_blurred' => 0,
		];

		// Handle synchronous submission using shared method.
		$row_id = $this->handle_sync_submission( $row->url, true, $additional_details ); // @phpstan-ignore-line

		if ( empty( $row_id ) ) {
			$error = new WP_Error( 'rest_not_found', __( 'Unable to reset performance test. Please try again.', 'rocket' ), [ 'status' => 404 ] );

			return rest_ensure_response( $error );
		}

		/**
		 * Fires when a performance monitoring job is reset/retested.
		 *
		 * @since 3.20
		 *
		 * @param int    $id The database row ID of the reset job.
		 */
		do_action( 'rocket_rocket_insights_job_retest', $request['id'] );

		$row = $this->query->get_row_by_id( $request['id'] );

		$data = [
			'success'           => true,
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
	 * Handle synchronous submission of Rocket Insights job.
	 *
	 * This method centralizes the logic for attempting synchronous job submission
	 * and falling back to async queuing when needed. It uses JobProcessor's send_api
	 * for the actual API call, then adds Rocket Insights-specific validation and logging.
	 *
	 * @since 3.20
	 *
	 * @param string $url               The URL to test.
	 * @param bool   $is_mobile         Whether this is a mobile test.
	 * @param array  $additional_details Optional additional data to store with the job.
	 *
	 * @return bool|null Row ID on success, false on failure, null if not allowed.
	 */
	private function handle_sync_submission( string $url, bool $is_mobile, array $additional_details = [] ) {
		// Attempt synchronous API submission.
		$sync_response = $this->job_processor->send_api( $url, $is_mobile, 'rocket_insights', true );

		// If sync submission failed or returned WP_Error, fall back to async queue.
		if ( false === $sync_response || empty( $sync_response['uuid'] ) ) {
			Logger::error(
				'Rocket Insights: Synchronous Submission failed, Now falling back to Async Queue.',
				[
					'url' => $url,
				]
			);
			return $this->manager->add_to_the_queue( $url, $is_mobile, $additional_details );
		}

		// Success! Save with the new data.
		$row_id = $this->manager->add_to_the_queue( $url, $is_mobile, $additional_details );

		if ( empty( $row_id ) ) {
			// DB insert failed after successful API submission - log orphaned job.
			Logger::error(
				'Rocket Insights: Database insert failed after successful sync submission',
				[
					'url'    => $url,
					'job_id' => $sync_response['uuid'],
				]
			);
			return false;
		}

		Logger::error(
			'Rocket Insights: Synchronous Submission successful, Now scheduling single job to run in 30 seconds.',
			[
				'url' => $url,
			]
		);

		// Update to in-progress status immediately with job_id.
		$this->manager->make_status_inprogress( $url, $is_mobile, 'rocket_insights', [ 'job_id' => $sync_response['uuid'] ] );
		$this->queue->schedule_job_status_single_task( time() + 30, $url, $is_mobile, 'rocket_insights' );

		return $row_id;
	}

	/**
	 * Checks if a given request has access to update a specific item.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return true|WP_Error
	 */
	public function update_item_permissions_check( $request ) {
		if ( ! $this->context->is_allowed() ) {
			return new WP_Error( 'rest_forbidden', __( 'You are not allowed to update this item.', 'rocket' ), [ 'status' => 403 ] );
		}

		return true;
	}

	/**
	 * Retrieves a collection of items.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return WP_REST_Response|WP_Error
	 */
	public function get_items( $request ) {
		$items = $this->query->query();

		if ( empty( $items ) ) {
			$error = new WP_Error( 'rest_not_found', 'No items found.', [ 'status' => 404 ] );

			return rest_ensure_response( $error );
		}

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
			$error = new WP_Error( 'rest_invalid_param', 'ids empty', [ 'status' => 400 ] );

			return rest_ensure_response( $error );
		}

		$query_params = [
			'id__in' => $request['ids'],
		];

		$results = $this->query->query( $query_params );

		// Result is empty.
		if ( empty( $results ) ) {
			$error = new WP_Error( 'rest_not_found', 'No rows found in DB for ids: ' . implode( ',', $request['ids'] ), [ 'status' => 404 ] );

			return rest_ensure_response( $error );
		}

		foreach ( $results as $result ) {
			$result->html = $this->render->get_performance_monitoring_list_row( $result );
		}

		$payload['success']           = true;
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
			'data'          => [
				'status' => 400,
			],
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
			$payload['error'] = true;

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
			$this->plan->max_urls() - (int) $this->query->get_total_count()
		);
	}

	/**
	 * Get the error message for when page limit is reached.
	 *
	 * @since 3.17
	 *
	 * @return string The formatted error message.
	 */
	private function get_page_limit_error_message(): string {
		if ( $this->context->is_free_user() ) {
			$upgrade_url = admin_url( 'options-general.php?page=' . WP_ROCKET_PLUGIN_SLUG . '&rocket_source=notice_free_page_limit_reached#rocket_insights' );

			return sprintf(
				/* translators: %1$s: opening <strong> tag, %2$s: closing </strong> tag, %3$s: opening link tag, %4$s: closing link tag */
				__( "You've %1\$sreached your free limit%2\$s. %3\$sUpgrade to continue%4\$s.", 'rocket' ),
				'<strong>',
				'</strong>',
				'<a href="' . esc_url( $upgrade_url ) . '">',
				'</a>'
			);
		}

		return sprintf(
			/* translators: %1$s: opening <strong> tag, %2$s: closing </strong> tag */
			__( "You've %1\$sreached the page limit%2\$s. Please remove at least one page to continue.", 'rocket' ),
			'<strong>',
			'</strong>'
		);
	}
}
