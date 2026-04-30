<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\CDN\RocketCDN;

use WP_Error;
use WP_REST_Controller;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Admin\Options_Data;

/**
 * REST API controller for RocketCDN free-tier page management.
 *
 * @since 3.22
 */
class Rest extends WP_REST_Controller {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';
	const ROUTE_BASE      = 'rocketcdn';
	const FREE_PAGE_LIMIT = 3;

	/**
	 * APIClient instance.
	 *
	 * @var APIClient
	 * @phpstan-ignore-next-line
	 */
	private $api_client;

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
	 * Constructor.
	 *
	 * @param APIClient      $api_client APIClient instance.
	 * @param RocketCDNQuery $query      RocketCDNQuery instance.
	 * @param Options_Data   $options    WP Rocket options instance.
	 */
	public function __construct( APIClient $api_client, RocketCDNQuery $query, Options_Data $options ) {
		$this->api_client = $api_client;
		$this->query      = $query;
		$this->options    = $options;
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
			self::ROUTE_BASE . '/state',
			[
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => [ $this, 'save_state' ],
				'permission_callback' => [ $this, 'check_permission' ],
				'args'                => [
					'active_driver' => [
						'required'          => false,
						'validate_callback' => function ( $param ) {
							return in_array( $param, [ 'builtin', 'byocdn', 'rocketcdn' ], true );
						},
						'sanitize_callback' => 'sanitize_text_field',
					],
					'paused'        => [
						'required'          => false,
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
	 * Validates the URL, checks the page limit, calls the SaaS API, and saves to DB.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response|WP_Error
	 */
	public function add_page( WP_REST_Request $request ) {
		$url = $request->get_param( 'url' );

		if ( $this->is_limit_reached() ) {
			return new WP_Error(
				'rocketcdn_page_limit_reached',
				sprintf(
					/* translators: %d: maximum number of free pages */
					__( 'Page limit of %d has been reached. Remove a page before adding a new one.', 'rocket' ),
					self::FREE_PAGE_LIMIT
				),
				[ 'status' => 400 ]
			);
		}

		if ( ! $this->check_if_url_is_valid( $url ) ) {
			return new WP_Error(
				'rocketcdn_invalid_url',
				__( 'The specified URL is not a valid page on this site.', 'rocket' ),
				[ 'status' => 400 ]
			);
		}

		$existing = $this->query->get_by_url( $url );

		if ( false !== $existing ) {
			return new WP_Error(
				'rocketcdn_page_already_exists',
				__( 'This page is already registered for RocketCDN delivery.', 'rocket' ),
				[ 'status' => 409 ]
			);
		}

		$page_title = trailingslashit( $url ) === trailingslashit( home_url() )
							? get_bloginfo( 'name' )
							: get_the_title( url_to_postid( $url ) );

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

		return new WP_REST_Response( $this->get_pages_data(), 201 );
	}

	/**
	 * Checks whether the given URL exists as a published page.
	 *
	 * Accepts the homepage and any URL that maps to a published post or page.
	 * External URLs and URLs with no matching WordPress content are rejected.
	 *
	 * @param string $url URL to check.
	 * @return bool
	 */
	private function check_if_url_is_valid( string $url ): bool {
		$home = untrailingslashit( home_url() );

		if ( 0 !== strpos( $url, $home ) ) {
			return false;
		}

		if ( $url === $home ) {
			return true;
		}

		return url_to_postid( $url ) > 0;
	}

	/**
	 * Removes a page from RocketCDN free-tier delivery by DB record ID.
	 *
	 * Deletes the DB record, calls the SaaS to unregister, and returns the updated list.
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

		$this->query->delete_item( $id );

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

		return $this->add_page( $request );
	}

	/**
	 * Saves CDN driver state options.
	 *
	 * Persists the active driver tab selection and/or paused state so the UI
	 * can restore the correct view after a page refresh.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response
	 */
	public function save_state( WP_REST_Request $request ): WP_REST_Response {
		$active_driver = $request->get_param( 'active_driver' );
		$paused        = $request->get_param( 'paused' );

		if ( null !== $active_driver ) {
			$this->options->set( 'rocketcdn_active_driver', $active_driver );
		}

		if ( null !== $paused ) {
			$this->options->set( 'rocketcdn_paused', (int) $paused );
		}

		return new WP_REST_Response(
			[
				'active_driver' => $this->options->get( 'rocketcdn_active_driver', 'rocketcdn' ),
				'paused'        => (int) $this->options->get( 'rocketcdn_paused', 0 ),
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
		return $this->query->get_total_count() >= self::FREE_PAGE_LIMIT;
	}

	/**
	 * Builds the pages response payload.
	 *
	 * @return array
	 */
	private function get_pages_data(): array {
		$pages = $this->query->query( [] );

		return [
			'pages' => array_map(
				function ( $page ) {
					return [
						'id'    => (int) $page->id,
						'url'   => $page->url,
						'title' => $page->title,
					];
				},
				is_array( $pages ) ? $pages : []
			),
			'count' => $this->query->get_total_count( false ),
			'limit' => self::FREE_PAGE_LIMIT,
		];
	}
}
