<?php

namespace WP_Rocket\Engine\Optimization\CSS\CriticalPath;

use WP_REST_Request;
use WP_Rocket\Event_Management\Subscriber_Interface;

class RestGenerate implements Subscriber_Interface {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	public static function get_subscribed_events() {
		return [
			'rest_api_init' => [
				[ 'register_generate_route' ],
			],
		];
	}

	public function register_generate_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'cpcss/post/(?P<id>[\d]+)',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'generate' ],
				'permission_callback' => function() {
					return current_user_can( 'rocket_regenerate_critical_css' );
				},
			]
		);
	}

	public function generate( WP_REST_Request $request ) {
		$params = $request->get_body_params();

		if ( 'publish' !== get_post_status( $request['id'] ) ) {
			return rest_ensure_response(
				[
					'code'    => 'post_not_published',
					'message' => __( 'Cannot generate CPCSS for unpublished post', 'rocket' ),
					'data'    => [
						'status' => 400,
					],
				]
			);
		}

		$post_url = get_permalink( $params['post_id'] );

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

		$response = [
			'code'    => 'success',
			'message' => __( 'Post Found', 'rocket' ),
			'data'    => [
				'status' => 200,
			],
		];

		return rest_ensure_response( $response );
	}
}
