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
use WP_Rocket\Engine\Common\{
	Utils,
	Page\PageHandlerTrait
};

/**
 * REST API controller for RocketCDN free-tier page management.
 *
 * @since 3.22
 */
class Rest extends WP_REST_Controller {
	use PageHandlerTrait;

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
	 * Constructor.
	 *
	 * @param RocketCDNQuery $query      RocketCDNQuery instance.
	 * @param Options_Data   $options    WP Rocket options instance.
	 * @param Options        $options_api WP Options API instance.
	 */
	public function __construct( RocketCDNQuery $query, Options_Data $options, Options $options_api ) {
		$this->query       = $query;
		$this->options     = $options;
		$this->options_api = $options_api;
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

		$payload = $this->get_page_url_validation_payload( $url );

		if ( $payload['error'] ) {
			return new WP_Error(
				'rocketcdn_url_not_found',
				$payload['message'],
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

		if ( Utils::is_home( $url ) ) {
			$page_title = __( 'Homepage', 'rocket' );
		} else {
			$page_title = $this->get_page_title( $payload['message'] );
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

		return new WP_REST_Response( $this->get_pages_data(), 201 );
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
	 * Persists the paused state.
	 *
	 * @param WP_REST_Request $request REST request.
	 * @return WP_REST_Response
	 */
	public function save_pause_state( WP_REST_Request $request ): WP_REST_Response {
		$paused = $request->get_param( 'paused' );

		if ( null !== $paused ) {
			$this->options->set( 'cdn', (int) $paused );
		}

		$this->options_api->set( 'settings', $this->options->get_options() );

		return new WP_REST_Response(
			[
				'paused' => (int) $this->options->get( 'cdn', 0 ),
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
			'limit' => $this->get_free_page_limit(),
		];
	}

	/**
	 * Return the total number of free pages allowed for RocketCDN delivery.
	 *
	 * @return int
	 */
	protected function get_free_page_limit(): int {
		return 3;
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
		$cdn_type                    = $request->get_param( 'driver' );
		$current_options             = $this->options_api->get( 'settings', [] );
		$current_options['cdn_type'] = $cdn_type;

		$this->options_api->set( 'settings', $current_options );

		return new WP_REST_Response(
			[
				'cdn_type' => $cdn_type,
			],
			200
		);
	}
}
