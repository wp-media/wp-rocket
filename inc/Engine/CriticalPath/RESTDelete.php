<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_REST_Request;
use WP_Rocket\Event_Management\Subscriber_Interface;

class RESTDelete implements Subscriber_Interface {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Critical CSS path.
	 *
	 * @var string
	 */
	protected $critical_css_path;

	/**
	 * Constructor
	 *
	 * @param string $critical_css_path Base critical CSS path.
	 */
	public function __construct( $critical_css_path ) {
		$this->critical_css_path = $critical_css_path . get_current_blog_id() . '/posts/';
	}

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.6
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init' => [ 'register_delete_route' ],
		];
	}

	/**
	 * Register Delete CPCSS route in the WP REST API.
	 *
	 * @since  3.6
	 */
	public function register_delete_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'cpcss/post/(?P<id>[\d]+)',
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete' ],
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);
	}

	/**
	 * Delete Post ID CPCSS file.
	 *
	 * @since 3.6
	 *
	 * @param WP_REST_Request $request the WP Rest Request object.
	 *
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $request ) {
		$post_id                = $request['id'];
		$post_type              = get_post_type( $post_id );
		$critical_css_file_path = "{$this->critical_css_path}{$post_type}-{$post_id}.css";
		$filesystem             = rocket_direct_filesystem();

		if ( empty( get_permalink( $post_id ) ) ) {
			return rest_ensure_response(
				[
					'code'    => 'post_not_exists',
					'message' => __( 'Requested post does not exist.', 'rocket' ),
					'data'    => [
						'status' => 400,
					],
				]
			);
		}

		if ( ! $filesystem->exists( $critical_css_file_path ) ) {
			return rest_ensure_response(
				[
					'code'    => 'cpcss_not_exists',
					'message' => __( 'Critical CSS file does not exist', 'rocket' ),
					'data'    => [
						'status' => 400,
					],
				]
			);
		}

		if ( ! $filesystem->delete( $critical_css_file_path ) ) {
			return rest_ensure_response(
				[
					'code'    => 'cpcss_deleted_failed',
					'message' => __( 'Critical CSS file cannot be deleted.', 'rocket' ),
					'data'    => [
						'status' => 400,
					],
				]
			);
		}

		return rest_ensure_response(
			[
				'code'    => 'success',
				'message' => __( 'Critical CSS file deleted successfully.', 'rocket' ),
				'data'    => [
					'status' => 200,
				],
			]
		);
	}

	/**
	 * Checks user's permissions. This is a callback registered to REST route's "permission_callback" parameter.
	 *
	 * @since 3.6
	 *
	 * @return bool true if the user has permission; else false.
	 */
	public function check_permissions() {
		return current_user_can( 'rocket_regenerate_critical_css' );
	}
}
