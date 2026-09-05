<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Admin\{
	Options_Data,
	Options
};
use WP_Rocket\Engine\CDN\Render\Controller as RenderController;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\Common\{
	Utils,
	Page\PageHandlerTrait
};
use WP_Rocket\Engine\Tracking\TrackingTrait;

/**
 * REST API controller for RocketCDN free-tier page management.
 *
 * @since 3.22
 */
class Rest extends WP_REST_Controller {
	use PageHandlerTrait;
	use TrackingTrait;

	const ROUTE_NAMESPACE = 'wp-rocket/v1';
	const ROUTE_BASE      = 'rocketcdn';

	/**
	 * RocketCDNQuery instance.
	 *
	 * @var RocketCDNQuery
	 */
	private $query;

	/**
	 * WP Rocket options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * WP Options API instance
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * CDN Render Controller instance.
	 *
	 * @var RenderController
	 */
	private $render_controller;

	/**
	 * CDN Context instance.
	 *
	 * @var Context
	 */
	private $context;

	/**
	 * Subscription controller.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * Constructor.
	 *
	 * @param RocketCDNQuery         $query             RocketCDNQuery instance.
	 * @param Options_Data           $options           WP Rocket options instance.
	 * @param Options                $options_api       WP Options API instance.
	 * @param RenderController       $render_controller CDN Render Controller instance.
	 * @param Context                $context           CDN Context instance.
	 * @param SubscriptionController $subscription_controller Subscription controller instance.
	 */
	public function __construct( RocketCDNQuery $query, Options_Data $options, Options $options_api, RenderController $render_controller, Context $context, SubscriptionController $subscription_controller ) {
		$this->query                   = $query;
		$this->options                 = $options;
		$this->options_api             = $options_api;
		$this->render_controller       = $render_controller;
		$this->context                 = $context;
		$this->subscription_controller = $subscription_controller;
	}

	/**
	 * Registers the REST routes.
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
					'callback'            => [ $this, 'get_pages' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
				[
					'methods'             => WP_REST_Server::CREATABLE,
					'callback'            => [ $this, 'add_page' ],
					'permission_callback' => [ $this, 'check_permission' ],
					'args'                => [
						'url'                => [
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return ! empty( $param ) && wp_http_validate_url( esc_url_raw( $param ) );
							},
							'sanitize_callback' => function ( $param ) {
								return $this->normalize_url_path_encoding( untrailingslashit( esc_url_raw( $param ) ) );
							},
						],
						'confirm_activation' => [
							'required'          => false,
							'default'           => false,
							'sanitize_callback' => 'rest_sanitize_boolean',
						],
					],
				],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/pages/homepage',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'add_homepage' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'confirm_activation' => [
						'required'          => false,
						'default'           => false,
						'sanitize_callback' => 'rest_sanitize_boolean',
					],
				],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/pages/(?P<id>\d+)',
			[
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => [ $this, 'remove_page' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'id' => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_numeric( $param ) && $param > 0;
						},
						'sanitize_callback' => 'absint',
					],
				],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/driver',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_cdn_type' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'driver' => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return in_array( $param, [ 'byocdn', 'rocketcdn' ], true );
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/mode',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_cdn_mode' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'mode' => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return in_array(
								$param,
								[
									Context::ROCKETCDN_FREE_TYPE,
									Context::ROCKETCDN_PAID_TYPE,
									Context::BYOCDN_TYPE,
									Context::CDN_STATE_NOTHING,
								],
								true
								);
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
				],
			]
		);

		register_rest_route(
			self::ROUTE_NAMESPACE,
			self::ROUTE_BASE . '/subscription',
			[
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_subscription' ],
					'permission_callback' => [ $this, 'check_permission' ],
				],
			]
		);
	}

	/**
	 * Checks if the current user has permission to manage CDN options.
	 *
	 * @return bool
	 */
	public function check_permission(): bool {
		return current_user_can( 'rocket_manage_options' );
	}

	/**
	 * Adds a page URL to RocketCDN free-tier delivery.
	 *
	 * CDN activation rules:
	 *  - CDN already in free mode   → page added, CDN untouched.
	 *  - CDN off, first page        → CDN activated automatically.
	 *  - CDN off, 2nd+ page         → page added, CDN left off.
	 *  - BYOCDN / other active mode → confirmation prompt (409), then switch on confirm.
	 *
	 * @param WP_REST_Request $request REST request. Accepts 'url' and 'confirm_activation'.
	 * @return WP_REST_Response|WP_Error
	 */
	public function add_page( WP_REST_Request $request ) {
		$url = $request->get_param( 'url' );

		if ( 'local' === wp_get_environment_type() ) {
			return new WP_Error(
				'rocketcdn_on_local_environment',
				__( 'Addition of pages to RocketCDN is disabled for local environment.', 'rocket' ),
				[ 'status' => 400 ]
			);
		}

		$should_activate = $this->resolve_cdn_activation();
		if ( is_wp_error( $should_activate ) ) {
			return $should_activate;
		}

		if ( $this->is_limit_reached() ) {
			return new WP_Error(
				'rocketcdn_page_limit_reached',
				sprintf(
					/* translators: %d: maximum number of free pages */
					__( 'Page limit of %d has been reached. Remove a page before adding a new one.', 'rocket' ),
					$this->get_free_page_limit()
				),
				[ 'status' => 400 ]
			);
		}

		$page_data = $this->validate_page_for_add( $url );
		if ( is_wp_error( $page_data ) ) {
			return $page_data;
		}

		if ( $should_activate ) {
			$created = $this->subscription_controller->create_subscription();
			if ( is_wp_error( $created ) ) {
				return $created;
			}
		}

		if ( ! wpm_apply_filters_typed( 'boolean', 'rocket_cdnfree_can_add_page', true, $url ) ) {
			return new WP_Error(
				'rocketcdn_disabled_by_filter',
				__( 'Adding page is disabled by the filter.', 'rocket' ),
				[ 'status' => 500 ]
			);
		}

		$inserted = $this->query->add_item( $page_data );
		if ( ! $inserted ) {
			return new WP_Error(
				'rocketcdn_db_error',
				__( 'Failed to save page to the database.', 'rocket' ),
				[ 'status' => 500 ]
			);
		}

		if ( $should_activate ) {
			$this->finalize_cdn_activation( (int) $inserted );

			/**
			 * Fires after RocketCDN free mode is activated and the subscription is confirmed active.
			 *
			 * The page cache may have been rebuilt without CDN URL replacement in the window between
			 * the admin settings save (which triggers an early cache clear) and this point where the
			 * subscription is confirmed live.
			 */
			do_action( 'rocket_cdnfree_activated' );
		}

		$this->clean_url_cache( $url );
		$this->track_event( 'Button Clicked', $this->build_add_page_event( $url, $request ) );

		return new WP_REST_Response(
			array_merge( $this->get_pages_data(), [ 'free_activated' => $should_activate ] ),
			201
		);
	}

	/**
	 * Decides whether CDN Free mode should be activated when adding a page.
	 *
	 * @return bool|WP_Error True to activate, false to skip, WP_Error to abort.
	 */
	private function resolve_cdn_activation() {
		$cdn_state = $this->options->get( 'cdn_state', Context::CDN_STATE_NOTHING );

		if ( Context::ROCKETCDN_FREE_TYPE === $cdn_state && $this->subscription_controller->has_active_subscription() ) {
			return false;
		}

		// Pages already exist — add without activating or prompting, regardless of current CDN mode.
		if ( $this->query->get_total_count( false ) > 0 ) {
			return false;
		}

		// First page — activate if allowed.
		if ( $this->render_controller->should_reject_rocketcdn_activation() ) {
			return new WP_Error(
				'cdn_mode_forced_off',
				__( 'RocketCDN cannot be activated in its current state.', 'rocket' ),
				[ 'status' => 403 ]
			);
		}

		return true;
	}

	/**
	 * Validates the URL and checks for an existing record, returning the data array for add_item().
	 *
	 * @param string $url Page URL to validate.
	 * @return array|WP_Error Row data ready for add_item(), or WP_Error on failure.
	 */
	private function validate_page_for_add( string $url ) {
		$payload = $this->get_url_validation_payload( $url );
		if ( $payload['error'] ) {
			return new WP_Error(
				'rocketcdn_url_not_found',
				$payload['message'],
				[ 'status' => $payload['data']['status'] ]
			);
		}

		if ( false !== $this->query->get_by_url( $url ) ) {
			return new WP_Error(
				'rocketcdn_page_already_exists',
				__( 'This page is already registered for RocketCDN delivery.', 'rocket' ),
				[ 'status' => 409 ]
			);
		}

		return [
			'url'           => $url,
			'title'         => Utils::is_home( $url ) ? __( 'Homepage', 'rocket' ) : $this->get_page_title( $payload['message'] ),
			'modified'      => current_time( 'mysql' ),
			'last_accessed' => current_time( 'mysql' ),
		];
	}

	/**
	 * Switches CDN to Free mode and registers the page for rollback if the async subscription job fails.
	 *
	 * @param int $page_id Inserted page DB record ID.
	 * @return void
	 */
	private function finalize_cdn_activation( int $page_id ): void {
		$this->apply_cdn_mode( Context::ROCKETCDN_FREE_TYPE );

		$async_task_id = $this->subscription_controller->get_last_async_task_id();
		if ( null !== $async_task_id ) {
			set_transient( 'rocket_cdnfree_pending_page_' . $async_task_id, $page_id, DAY_IN_SECONDS );
		}
	}

	/**
	 * Builds the tracking event payload for a successful page add.
	 *
	 * @param string          $url     Page URL that was added.
	 * @param WP_REST_Request $request Original REST request (carries 'source').
	 * @return array
	 */
	private function build_add_page_event( string $url, WP_REST_Request $request ): array {
		$source_raw = $request->get_param( 'source' );
		$source     = is_string( $source_raw ) && '' !== $source_raw ? sanitize_key( $source_raw ) : 'manual';

		if ( Utils::is_home( $url ) ) {
			return [
				'button' => 'rocket cdn add homepage',
				'source' => $source,
			];
		}

		return [
			'button'      => 'rocket cdn add page',
			'is_homepage' => false,
			'pages_count' => $this->query->get_total_count( false ),
			'source'      => $source,
		];
	}

	/**
	 * Cleans the cache for the provided URL to ensure changes are reflected in RocketCDN delivery.
	 *
	 * @param string $url URL to clear.
	 *
	 * @return void
	 */
	private function clean_url_cache( string $url ): void {
		if ( Utils::is_home( $url ) ) {
			rocket_clean_home();

			return;
		}

		rocket_clean_files( [ user_trailingslashit( $url ) ] );
	}

	/**
	 * Removes a page from RocketCDN free-tier delivery by DB record ID.
	 *
	 * Deletes the DB record and returns the updated list.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function remove_page( WP_REST_Request $request ) {
		$id   = $request->get_param( 'id' );
		$item = $this->query->get_item( $id );

		if ( empty( $item ) ) {
			return new WP_Error(
				'rocketcdn_page_not_found',
				__( 'The specified page was not found.', 'rocket' ),
				[ 'status' => 404 ]
			);
		}

		$removed_url = $item->url;

		$this->query->delete_item( $id );

		$this->clean_url_cache( $removed_url );

		$pages_count = $this->query->get_total_count( false );

		$this->track_event(
			'Button Clicked',
			[
				'button'      => 'rocket cdn remove page',
				'is_homepage' => Utils::is_home( $removed_url ),
				'pages_count' => $pages_count,
			]
		);

		return new WP_REST_Response( $this->get_pages_data(), 200 );
	}

	/**
	 * Rolls back a failed async subscription creation.
	 *
	 * Deletes the page record (if one was associated) and resets CDN mode to 'nothing'.
	 * Called by RESTSubscriber when rocket_cdnfree_subscription_creation_failed fires.
	 *
	 * @param int|null $page_id Page DB record ID to remove, or null if none was recorded.
	 * @return void
	 */
	public function rollback_failed_subscription( ?int $page_id ): void {
		if ( null !== $page_id ) {
			$this->query->delete_item( $page_id );
		}

		$this->apply_cdn_mode( Context::CDN_STATE_NOTHING );
	}

	/**
	 * Returns all registered free-tier pages with count and limit info.
	 *
	 * @return WP_REST_Response
	 */
	public function get_pages(): WP_REST_Response {
		return new WP_REST_Response( $this->get_pages_data(), 200 );
	}

	/**
	 * Quick-adds the site homepage as a free-tier CDN page.
	 *
	 * @param WP_REST_Request $incoming_request REST request (carries 'confirm_activation' when re-submitted after the activation prompt).
	 * @return WP_REST_Response|WP_Error
	 */
	public function add_homepage( WP_REST_Request $incoming_request ) {
		$request = new WP_REST_Request( 'POST' );
		$request->set_param( 'url', untrailingslashit( home_url() ) );
		$request->set_param( 'source', 'add_homepage_button' );
		$request->set_param( 'confirm_activation', $incoming_request->get_param( 'confirm_activation' ) );

		return $this->add_page( $request );
	}

	/**
	 * Saves CDN driver state options.
	 *
	/**
	 * Checks whether the free-tier page limit has been reached.
	 *
	 * @return bool True if the count is at or above the limit.
	 */
	public function is_limit_reached(): bool {
		return $this->query->get_total_count() >= $this->get_free_page_limit();
	}

	/**
	 * Builds the pages response payload.
	 *
	 * @return array
	 */
	private function get_pages_data(): array {
		$pages = $this->query->get_all();

		$pages_count = $this->query->get_total_count( false );

		return [
			'pages'                            => array_map(
				function ( $page ) {
					return [
						'id'    => (int) $page->id,
						'url'   => $page->url,
						'title' => $page->title,
					];
				},
				$pages
			),
			'count'                            => $pages_count,
			'limit'                            => $this->get_free_page_limit(),
			'items_html'                       => $this->render_controller->get_built_in_page_list(),
			'status_indicator_html'            => $this->render_controller->get_status_indicator_html( $pages_count ),
			'is_subscription_creation_loading' => $this->subscription_controller->is_subscription_creation_loading(),
		];
	}

	/**
	 * Return the total number of free pages allowed for RocketCDN delivery.
	 *
	 * @return int
	 */
	protected function get_free_page_limit(): int {
		return $this->context->get_free_page_limit();
	}

	/**
	 * Activates or deactivates a CDN mode via the toggle.
	 *
	 * Accepts 'rocketcdn_free', 'byocdn', or 'nothing' (deactivate all).
	 * Rejects activation of RocketCDN (free or paid) when it is in a forced-off state.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function save_cdn_mode( WP_REST_Request $request ) {
		$mode = $request->get_param( 'mode' );

		$rocketcdn_types = [ Context::ROCKETCDN_FREE_TYPE, Context::ROCKETCDN_PAID_TYPE ];

		if ( in_array( $mode, $rocketcdn_types, true ) && $this->render_controller->should_reject_rocketcdn_activation() ) {
			return new WP_Error(
				'cdn_mode_forced_off',
				__( 'RocketCDN cannot be activated in its current state.', 'rocket' ),
				[ 'status' => 403 ]
			);
		}

		if ( Context::ROCKETCDN_PAID_TYPE === $mode && ! $this->subscription_controller->is_paid() ) {
			return new WP_Error(
				'cdn_mode_paid_subscription_required',
				__( 'A RocketCDN paid subscription is required to activate this mode.', 'rocket' ),
				[ 'status' => 403 ]
			);
		}

		$this->apply_cdn_mode( $mode );

		return new WP_REST_Response(
			[
				'applied_cdn_state'           => $this->context->get_applied_cdn_state( $mode ),
				'rocketcdn_state'             => $this->context->get_rocketcdn_state( $mode ),
				'disable_rocket_cdn_elements' => $this->render_controller->should_disable_element_for_rocketcdn(),
			],
			200
		);
	}

	/**
	 * Persists a CDN mode and fires the associated change action.
	 *
	 * Shared by {@see save_cdn_mode()} and the activation-prompt/auto-activation
	 * flow in {@see add_page()}, so both paths apply a mode change identically.
	 *
	 * @param string $mode The CDN mode to apply ('rocketcdn_free', 'rocketcdn_paid', 'byocdn', or 'nothing').
	 * @return void
	 */
	private function apply_cdn_mode( string $mode ): void {
		$this->options->set( 'cdn_state', $mode );
		$this->options->set( 'cdn', (int) ( Context::CDN_STATE_NOTHING !== $mode ) );
		$this->options->set( 'cdn_type', Context::BYOCDN_TYPE === $mode ? 'byocdn' : 'rocketcdn' );
		$this->options_api->set( 'settings', $this->options->get_options() );

		/**
		 * Fires after the CDN mode is changed via the toggle.
		 *
		 * @param string $mode The new CDN mode ('rocketcdn_free', 'rocketcdn_paid', 'byocdn', or 'nothing').
		 */
		do_action( 'rocket_cdn_mode_changed', $mode );
	}

	/**
	 * Save cdn driver
	 *
	 * Persists the active driver tab selection so the UI
	 *  can restore the correct view after a page refresh.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response
	 */
	public function save_cdn_type( WP_REST_Request $request ) {
		$cdn_type = $request->get_param( 'driver' );

		$this->options->set( 'cdn_type', $cdn_type );
		$this->options_api->set( 'settings', $this->options->get_options() );

		return new WP_REST_Response(
			[
				'cdn_type'                    => $cdn_type,
				'disable_rocket_cdn_elements' => $this->render_controller->should_disable_element_for_rocketcdn(),
			],
			200
		);
	}

	/**
	 * Get subscription details.
	 *
	 * @return WP_REST_Response
	 */
	public function get_subscription(): WP_REST_Response {
		$subscription = $this->subscription_controller->get_subscription();

		if ( empty( $subscription ) ) {
			return new WP_REST_Response( null, 204 );
		}

		return new WP_REST_Response(
			$subscription,
			200
		);
	}

	/**
	 * Get URL validation payload.
	 *
	 * @param string $url URL to validate.
	 *
	 * @return array
	 */
	private function get_url_validation_payload( string $url ): array {
		$payload = $this->get_page_url_validation_payload( $url );

		// Check for same host.
		$url_host  = wp_parse_url( $url );
		$site_host = wp_parse_url( home_url() );

		// Check that URL has a valid host component.
		if ( ! isset( $url_host['host'] ) ) {
			$payload['error']   = true;
			$payload['message'] = __( 'Invalid URL provided.', 'rocket' );
		}

		// Check that URL host matches site host.
		if ( $url_host['host'] !== $site_host['host'] ) {
			$payload['error']   = true;
			$payload['message'] = __( 'URL must be on the same domain as the site.', 'rocket' );
		}

		return $payload;
	}
}
