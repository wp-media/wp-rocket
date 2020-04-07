<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_REST_Request;
use WP_Rocket\Event_Management\Subscriber_Interface;

class RESTDelete implements Subscriber_Interface {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.6
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init' => [
				[ 'register_delete_route' ],
			],
		];
	}

	/**
	 * Register Delete CPCSS route in the WP REST API.
	 *
	 * @since  3.6
	 *
	 * @return void
	 */
	public function register_delete_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'cpcss/post/(?P<id>[\d]+)',
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete' ],
				'permission_callback' => function() {
					return current_user_can( 'rocket_regenerate_critical_css' );
				},
			]
		);
	}
	/**
	 * Delete Post ID CPCSS file.
	 *
	 * @since 3.6
	 *
	 * @param  WP_REST_Request $request the WP Rest Request object.
	 * @return WP_REST_Response
	 */
	public function delete( WP_REST_Request $request ) {
		$post_id                = $request['id'];
		$post_url               = get_permalink( $post_id );
		$critical_css_file_path = rocket_get_constant( 'WP_ROCKET_CRITICAL_CSS_PATH' ) . get_current_blog_id() . '/posts/post-type-' . $post_id . '.css';

		if ( ! $post_url ) {
			return rest_ensure_response(
				[
					'code'    => 'post_not_exists',
					'message' => __( 'Requested post does not exist', 'rocket' ),
					'data'    => [
						'status' => 400,
					],
				]
			);
		}

		if ( ! rocket_direct_filesystem()->exists( $critical_css_file_path ) ) {
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

		rocket_direct_filesystem()->delete( $critical_css_file_path );

		$response = [
			'code'    => 'success',
			'message' => __( 'Critical CSS file deleted successfully.', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		return rest_ensure_response( $response );
	}
}
