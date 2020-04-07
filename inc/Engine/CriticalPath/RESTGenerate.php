<?php

namespace WP_Rocket\Engine\Optimization\CriticalPath;

use WP_REST_Request;
use WP_Rocket\Event_Management\Subscriber_Interface;

class RestGenerate implements Subscriber_Interface {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';
	const API_URL         = 'https://cpcss.wp-rocket.me/api/job/';

	/**
	 * Base critical CSS path for posts
	 *
	 * @var string
	 */
	private $critical_css_path;

	/**
	 * Constructor
	 *
	 * @param string $critical_css_path Base critical CSS path.
	 */
	public function __construct( $critical_css_path ) {
		$this->critical_css_path = $critical_css_path . get_current_blog_id() . '/posts/';
	}

	/**
	 * Events
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init' => [
				[ 'register_generate_route' ],
			],
		];
	}

	/**
	 * Registers the generate route in the WP REST API
	 *
	 * @since 3.6
	 *
	 * @return void
	 */
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

	/**
	 * Generates the CPCSS for the requested post ID
	 *
	 * @since 3.6
	 *
	 * @param WP_REST_Request $request WP REST request response.
	 * @return WP_REST_Response
	 */
	public function generate( WP_REST_Request $request ) {
		$params = $request->get_body_params();

		if ( 'publish' !== get_post_status( $params['id'] ) ) {
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

		$post_url  = get_permalink( $params['id'] );
		$post_type = get_post_type( $params['id'] );

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

		$response = $this->send_generation_request( $post_url, $post_type );

		if ( false === $response->success ) {
			return rest_ensure_response( $response );
		}

		while ( $job_data = $this->get_critical_path( $response->data->id ) ) { // phpcs:ignore WordPress.CodeAnalysis.AssignmentInCondition.FoundInWhileCondition
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

			if ( isset( $job_data->data->state, $job_data->data->critical_path ) && 'complete' === $job_data->data->state ) {
				$result = $this->save_post_cpcss( $params['id'], $post_type, $job_data->data->critical_path );

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
	 * Sends a generation request to the Critical Path API
	 *
	 * @since 3.6
	 *
	 * @param string $post_url  The post URL.
	 * @param string $post_type The post type.
	 * @return object
	 */
	protected function send_generation_request( $post_url, $post_type ) {
		$response = wp_remote_post(
			self::API_URL,
			[
				// This filter is documented in inc/Engine/CriticalPath/CriticalCSSGeneration.php.
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

			return (object) [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				'message' => $error,
				'data'    => [
					'status' => 400,
				],
			];
		}

		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			return (object) [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				// translators: %1$s = post URL.
				'message' => sprintf( __( 'Critical CSS for %1$s not generated. Error: The API returned an invalid response code.', 'rocket' ), $post_url ),
				'data'    => [
					'status' => 400,
				],
			];
		}

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! isset( $data->data, $data->data->id ) ) {
			return (object) [
				'success' => false,
				'code'    => 'cpcss_generation_failed',
				// translators: %1$s = post URL.
				'message' => sprintf( __( 'Critical CSS for %1$s not generated. Error: The API returned an empty response.', 'rocket' ), $post_url ),
				'data'    => [
					'status' => 400,
				],
			];
		}

		return $data;
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

	/**
	 * Saves the CPCSS for the post
	 *
	 * @param int    $post_id   The post ID.
	 * @param string $post_type The post type.
	 * @param string $cpcss     The generated CPCSS.
	 * @return bool
	 */
	protected function save_post_cpcss( $post_id, $post_type, $cpcss ) {
		if ( ! rocket_direct_filesystem()->is_dir( $this->critical_css_path ) ) {
			rocket_mkdir_p( $this->critical_css_path );
		}

		$filepath = "{$this->critical_css_path}/{$post_type}-{$post_id}.css";

		return rocket_put_content( $filepath, wp_strip_all_tags( $cpcss, true ) );
	}
}
