<?php

namespace WP_Rocket\Engine\Optimization\CriticalPath;

use WP_REST_Request;
use WP_Rocket\Event_Management\Subscriber_Interface;

class RestGenerate implements Subscriber_Interface {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';
	const API_URL         = 'https://cpcss.wp-rocket.me/api/job/';

	/**
	 * Undocumented variable
	 *
	 * @var [type]
	 */
	private $critical_css_path;

	/**
	 * Undocumented function
	 *
	 * @param [type] $critical_css_path
	 */
	public function __construct( $critical_css_path ) {
		$this->critical_css_path = $critical_css_path . get_current_blog_id() . '/posts/';
	}

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

		$post_url  = get_permalink( $params['post_id'] );
		$post_type = get_post_type( $params['post_id'] );

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

		$response = wp_remote_post(
			self::API_URL,
			[
				/**
				 * Filters the parameters sent to the Critical CSS generator API
				 *
				 * @since 2.11
				 * @author Remy Perona
				 *
				 * @param array $params An array of parameters to send to the API.
				 * @param array $item The item to process.
				 */
				'body' => apply_filters(
					'rocket_cpcss_job_request',
					[
						'url' => $post_url,
					],
					[
						'url'  => $post_url,
						'type' => $post_type,
					]
				),
			]
		);

		if ( 400 === wp_remote_retrieve_response_code( $response ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ) );

			// translators: %1$s = post URL.
			$error = sprintf( __( 'Critical CSS for %1$s not generated.', 'rocket' ), $post_url );

			if ( isset( $data->message ) ) {
				// translators: %1$s = error message.
				$error .= ' ' . sprintf( __( 'Error: %1$s', 'rocket' ), $data->message );
			}

			return rest_ensure_response(
				[
					'code'    => 'cpcss_generation_failed',
					'message' => $error,
					'data'    => [
						'status' => 400,
					],
				]
			);
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return rest_ensure_response(
				[
					'code'    => 'cpcss_generation_failed',
					// translators: %1$s = post URL.
					'message' => sprintf( __( 'Critical CSS for %1$s not generated. Error: The API returned an invalid response code.', 'rocket' ), $post_url ),
					'data'    => [
						'status' => 400,
					],
				]
			);
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! isset( $data->data, $data->data->id ) ) {
			return rest_ensure_response(
				[
					'code'    => 'cpcss_generation_failed',
					// translators: %1$s = post URL.
					'message' => sprintf( __( 'Critical CSS for %1$s not generated. Error: The API returned an empty response.', 'rocket' ), $post_url ),
					'data'    => [
						'status' => 400,
					],
				]
			);
		}

		while ( $job_data = $this->get_critical_path( $data->data->id ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
			if ( 400 === (int) $job_data->status ) {
				return rest_ensure_response(
					[
						'code'    => 'cpcss_generation_failed',
						// translators: %1$s = post URL, %2$s = error message.
						'message' => sprintf( __( 'Critical CSS for %1$s not generated. Error: %2$s', 'rocket' ), $post_url, $job_data->message ),
						'data'    => [
							'status' => 400,
						],
					]
				);
			}

			if ( 'complete' === $job_data->data->state ) {
				if ( ! rocket_direct_filesystem()->is_dir( $this->critical_css_path ) ) {
					rocket_mkdir_p( $this->critical_css_path );
				}

				$file_path     = "{$this->critical_css_path}/{$post_type}-{$post_id}.css";
				$cpcss_content = wp_strip_all_tags( $job_data->data->critical_path, true );
				$result        = rocket_put_content( $file_path, $cpcss_content );

				if ( ! $result ) {
					return rest_ensure_response(
						[
							'code'    => 'cpcss_generation_failed',
							// translators: %1$s = post URL, %2$s = error message.
							'message' => sprintf(
								// translators: %1$s = post URL, %2$s = error message.
								__( 'Critical CSS for %1$s not generated. Error: %2$s', 'rocket' ),
								$post_url,
								// translators: %s = critical CSS directory path.
								sprintf( __( 'The critical CSS content could not be saved as a file in %s.', 'rocket' ), $this->critical_css_path )
							),
							'data'    => [
								'status' => 400,
							],
						]
					);
				}

				return rest_ensure_response( [
					'code'    => 'success',
					// translators: %s = post URL.
					'message' => sprintf( __( 'Critical CSS for %s generated.', 'rocket' ), $post_url ),
					'data'    => [
						'status' => 200,
					],
				] );
			}

			sleep( 2 );
		}
	}

	/**
	 * Gets the returned body of a request to a specific job from the Critical CSS generator API
	 *
	 * @since 2.11
	 * @author Remy Perona
	 *
	 * @param string $job_id Job identifier.
	 * @return object JSON decoded body of the request's response
	 */
	protected function get_critical_path( $job_id ) {
		$response = wp_remote_get(
			self::API_URL . $job_id . '/'
		);

		return json_decode( wp_remote_retrieve_body( $response ) );
	}
}
