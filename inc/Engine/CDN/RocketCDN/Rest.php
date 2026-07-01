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
						'url' => [
							'required'          => true,
							'validate_callback' => function ( $param ) {
								return ! empty( $param ) && wp_http_validate_url( esc_url_raw( $param ) );
							},
							'sanitize_callback' => function ( $param ) {
								return untrailingslashit( esc_url_raw( $param ) );
							},
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
			self::ROUTE_BASE . '/pause',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_pause_state' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'paused' => [
						'required'          => true,
						'validate_callback' => function ( $param ) {
							return is_bool( $param ) || in_array( (string) $param, [ '0', '1' ], true );
						},
						'sanitize_callback' => function ( $param ) {
							return (int) (bool) $param;
						},
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
	 * Validates the URL, checks the page limit, and saves to DB.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function add_page( WP_REST_Request $request ) {
		$url = $request->get_param( 'url' );

		// Check for local environment.
		if ( 'local' === wp_get_environment_type() ) {
			return new WP_Error(
				'rocketcdn_on_local_environment',
				__( 'Addition of pages to RocketCDN is disabled for local environment.', 'rocket' ),
				[ 'status' => 400 ]
			);
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

		$payload = $this->get_url_validation_payload( $url );

		if ( $payload['error'] ) {
			return new WP_Error(
				'rocketcdn_url_not_found',
				$payload['message'],
				[ 'status' => $payload['data']['status'] ]
			);
		}

		$page_title = __( 'Homepage', 'rocket' );

		if ( ! Utils::is_home( $url ) ) {
			$page_title = $this->get_page_title( $payload['message'] );
		}

		$existing = $this->query->get_by_url( $url );

		if ( false !== $existing ) {
			return new WP_Error(
				'rocketcdn_page_already_exists',
				__( 'This page is already registered for RocketCDN delivery.', 'rocket' ),
				[ 'status' => 409 ]
			);
		}

		$created = $this->subscription_controller->create_subscription();
		if ( is_wp_error( $created ) ) {
			return $created;
		}

		/**
		 * WP Rocket Metabox fields on post edit page.
		 *
		 * @param string[] $original_fields Metaboxes fields.
		 */
		if ( ! wpm_apply_filters_typed( 'boolean', 'rocket_cdnfree_can_add_page', true, $url ) ) {
			return new WP_Error(
				'rocketcdn_disabled_by_filter',
				__( 'Adding page is disabled by the filter.', 'rocket' ),
				[ 'status' => 500 ]
			);
		}

		$inserted = $this->query->add_item(
			[
				'url'           => $url,
				'title'         => $page_title,
				'modified'      => current_time( 'mysql' ),
				'last_accessed' => current_time( 'mysql' ),
			]
		);

		if ( ! $inserted ) {
			return new WP_Error(
				'rocketcdn_db_error',
				__( 'Failed to save page to the database.', 'rocket' ),
				[ 'status' => 500 ]
			);
		}

		$this->clean_url_cache( $url );

		$pages_count   = $this->query->get_total_count( false );
		$source_raw    = $request->get_param( 'source' );
		$source        = is_string( $source_raw ) && '' !== $source_raw ? sanitize_key( $source_raw ) : 'manual';
		$tracked_event = [
			'button'      => 'rocket cdn add page',
			'is_homepage' => Utils::is_home( $url ),
			'pages_count' => $pages_count,
			'source'      => $source,
		];

		if ( Utils::is_home( $url ) ) {
			$tracked_event['button'] = 'rocket cdn add homepage';
			unset( $tracked_event['is_homepage'] );
			unset( $tracked_event['pages_count'] );
		}

		$this->track_event( 'Button Clicked', $tracked_event );

		return new WP_REST_Response( $this->get_pages_data(), 201 );
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

		rocket_clean_files( [ $url ] );
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
	 * @return WP_REST_Response|WP_Error
	 */
	public function add_homepage() {
		$request = new WP_REST_Request( 'POST' );
		$request->set_param( 'url', untrailingslashit( home_url() ) );
		$request->set_param( 'source', 'add_homepage_button' );

		return $this->add_page( $request );
	}

	/**
	 * Saves CDN driver state options.
	 *
	 * Persists the paused state.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response
	 */
	public function save_pause_state( WP_REST_Request $request ): WP_REST_Response {
		$paused = (int) $request->get_param( 'paused' );

		$this->options->set( 'cdn', $paused );
		$this->options_api->set( 'settings', $this->options->get_options() );

		$status = 0 === $paused ? 'paused' : 'active';
		$action = 0 === $paused ? 'user_paused' : 'user_resume';

		do_action( 'rocket_rocketcdn_cdn_state_changed', $status, $action );

		return new WP_REST_Response(
			[
				'paused' => $this->options->get( 'cdn', 0 ),
			],
			200
		);
	}

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
