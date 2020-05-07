<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_REST_Request;
use WP_Rocket\Event_Management\Subscriber_Interface;

class RESTGenerate implements Subscriber_Interface {
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
	 * Return an array of events that this subscriber wants to listen to.
	 *
	 * @since  3.6
	 *
	 * @return array
	 */
	public static function get_subscribed_events() {
		return [
			'rest_api_init' => 'register_generate_route',
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
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);
	}

	/**
	 * Generates the CPCSS for the requested post ID
	 *
	 * @since 3.6
	 *
	 * @param  WP_REST_Request $request WP REST request response.
	 * @return WP_REST_Response
	 */
	public function generate( WP_REST_Request $request ) {
		$status = get_post_status( $request['id'] );

		if ( ! $status ) {
			return rest_ensure_response(
				$this->return_array_response(
					false,
					'post_not_exists',
					__( 'Requested post does not exist.', 'rocket' ),
					400
				)
			);
		}

		if ( 'publish' !== $status ) {
			return rest_ensure_response(
				$this->return_array_response(
					false,
					'post_not_published',
					__( 'Cannot generate CPCSS for unpublished post.', 'rocket' ),
					400
				)
			);
		}

		$post_url        = get_permalink( $request['id'] );
		$post_type       = get_post_type( $request['id'] );
		$cpcss_job_id    = get_transient( 'rocket_specific_cpcss_job_' . $request['id'] );
		$request_timeout = $request['timeout'];

		// Ajax call requested a timeout.
		if ( ! empty( $request_timeout ) ) {
			// Clean transient if the ajax call requested a timeout.
			$this->delete_job_id_cache( $request['id'] );

			return rest_ensure_response(
				$this->return_array_response(
					false,
					'cpcss_generation_timeout',
					// translators: %1$s = post URL.
					sprintf( __( 'Critical CSS for %1$s timeout. Please retry a little later.', 'rocket' ), $post_url ),
					400
				)
			);
		}

		if ( false !== $cpcss_job_id ) {
			return rest_ensure_response( $this->check_cpcss_job_status( $cpcss_job_id, $request['id'], $post_url, $post_type ) );
		}

		$job_creation = $this->send_generation_request( $post_url );

		if ( false === $job_creation['success'] ) {
			return rest_ensure_response( $job_creation );
		}

		set_transient( 'rocket_specific_cpcss_job_' . $request['id'], $job_creation['data']['id'] );

		return rest_ensure_response( $this->check_cpcss_job_status( $job_creation['data']['id'], $request['id'], $post_url, $post_type ) );
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

	/**
	 * Sends a generation request to the Critical Path API
	 *
	 * @since 3.6
	 *
	 * @param string $post_url The post URL.
	 * @return array
	 */
	protected function send_generation_request( $post_url ) {
		$response = wp_remote_post(
			self::API_URL,
			[
				// This filter is documented in inc/Engine/CriticalPath/CriticalCSSGeneration.php.
				'body' => apply_filters(
					'rocket_cpcss_job_request',
					[
						'url' => $post_url,
					]
				),
			]
		);

		$response_code = wp_remote_retrieve_response_code( $response );

		if ( in_array( $response_code, [ 400, 404 ], true ) ) {
			$data = json_decode( wp_remote_retrieve_body( $response ) );

			// translators: %1$s = post URL.
			$error = sprintf( __( 'Critical CSS for %1$s not generated.', 'rocket' ), $post_url );

			if ( isset( $data->message ) ) {
				// translators: %1$s = error message.
				$error .= ' ' . sprintf( __( 'Error: %1$s', 'rocket' ), $data->message );
			}

			return $this->return_array_response(
				false,
				'cpcss_generation_failed',
				// translators: %s = post URL.
				$error,
				400
			);
		}

		if ( 200 !== $response_code ) {
			return $this->return_array_response(
				false,
				'cpcss_generation_failed',
				// translators: %s = post URL.
				sprintf( __( 'Critical CSS for %1$s not generated. Error: The API returned an invalid response code.', 'rocket' ), $post_url ),
				$response_code
			);
		}

		$data = json_decode( wp_remote_retrieve_body( $response ), true );

		if ( ! isset( $data['data']['id'] ) ) {
			return $this->return_array_response(
				false,
				'cpcss_generation_failed',
				// translators: %s = post URL.
				sprintf( __( 'Critical CSS for %1$s not generated. Error: The API returned an empty response.', 'rocket' ), $post_url ),
				400
			);
		}

		return $data;
	}

	/**
	 * Checks CPCSS job status for the given ID.
	 *
	 * @since 3.6
	 *
	 * @param  int    $cpcss_job_id The CPCSS Job ID.
	 * @param  int    $post_id      The post ID.
	 * @param  string $post_url     The post URL.
	 * @param  string $post_type    The post type.
	 * @return array
	 */
	protected function check_cpcss_job_status( $cpcss_job_id, $post_id, $post_url, $post_type ) {
		$job_data = $this->get_critical_path( $cpcss_job_id );

		if ( in_array( (int) $job_data->status, [ 400, 404 ], true ) ) {
			return $this->on_job_error( $post_id, $post_url, $job_data );
		}

		if ( isset( $job_data->data->state ) && 'complete' !== $job_data->data->state ) {
			return $this->return_array_response(
				true,
				'cpcss_generation_pending',
				// translators: %s = post URL.
				sprintf( __( 'Critical CSS for %s in progress.', 'rocket' ), $post_url ),
				200
			);
		}

		if ( isset( $job_data->data->state, $job_data->data->critical_path ) && 'complete' === $job_data->data->state ) {
			return $this->on_job_success( $post_id, $post_url, $post_type, $job_data->data->critical_path );
		}
	}

	/**
	 * Gets the returned body of a request to a specific job from the Critical CSS generator API
	 *
	 * @since 3.6
	 *
	 * @param  string $job_id Job identifier.
	 * @return object         JSON decoded body of the request's response
	 */
	protected function get_critical_path( $job_id ) {
		$response = wp_remote_get(
			self::API_URL . "{$job_id}/"
		);

		$data = json_decode( wp_remote_retrieve_body( $response ) );

		if ( ! isset( $data->status ) ) {
			return (object) [
				'success' => false,
				'status'  => wp_remote_retrieve_response_code( $response ),
				'message' => __( 'The API returned an unexpected response.', 'rocket' ),
			];
		}

		return $data;
	}

	/**
	 * Deletes the job ID cache & return the response on error
	 *
	 * @since 3.6
	 *
	 * @param int    $post_id  The post ID.
	 * @param string $post_url The post URL.
	 * @param object $job_data The object returned by the HTTP request.
	 * @return array
	 */
	protected function on_job_error( $post_id, $post_url, $job_data ) {
		$this->delete_job_id_cache( $post_id );

		// translators: %1$s = post URL.
		$error = sprintf( __( 'Critical CSS for %1$s not generated.', 'rocket' ), $post_url );

		if ( isset( $job_data->message ) ) {
			// translators: %1$s = error message.
			$error .= ' ' . sprintf( __( 'Error: %1$s', 'rocket' ), $job_data->message );
		}

		return $this->return_array_response(
			false,
			'cpcss_generation_failed',
			$error,
			400
		);
	}

	/**
	 * Deletes the job ID cache, save the critical CSS in the file & return the response on success
	 *
	 * @since 3.6
	 *
	 * @param int    $post_id           The post ID.
	 * @param string $post_url          The post URL.
	 * @param string $post_type         The post type.
	 * @param string $critical_path_css The critical CSS.
	 * @return array
	 */
	protected function on_job_success( $post_id, $post_url, $post_type, $critical_path_css ) {
		$this->delete_job_id_cache( $post_id );

		if ( ! $this->save_post_cpcss( $post_id, $post_type, $critical_path_css ) ) {
			$error = sprintf(
				// translators: %1$s = post URL, %2$s = critical CSS directory path.
				__( 'Critical CSS for %1$s not generated. Error: The critical CSS content could not be saved as a file in %2$s', 'rocket' ),
				$post_url,
				$this->critical_css_path
			);

			return $this->return_array_response(
				false,
				'cpcss_generation_failed',
				$error,
				400
			);
		}

		return $this->return_array_response(
			true,
			'cpcss_generation_successful',
			// translators: %s = post URL.
			sprintf( __( 'Critical CSS for %s generated.', 'rocket' ), $post_url ),
			200
		);
	}

	/**
	 * Returns the formatted array response
	 *
	 * @since 3.6
	 *
	 * @param bool   $success True for success, false otherwise.
	 * @param string $code    The code to use for the response.
	 * @param string $message The message to send in the response.
	 * @param int    $status  The status code to send for the response.
	 * @return array
	 */
	protected function return_array_response( $success = false, $code = '', $message = '', $status = 200 ) {
		return [
			'success' => $success,
			'code'    => $code,
			'message' => wp_strip_all_tags( $message ),
			'data'    => [
				'status' => $status,
			],
		];
	}

	/**
	 * Deletes the cached job ID for the post
	 *
	 * @param int $post_id The post ID.
	 * @return void
	 */
	protected function delete_job_id_cache( $post_id = 0 ) {
		delete_transient( 'rocket_specific_cpcss_job_' . $post_id );
	}

	/**
	 * Saves the CPCSS for the post
	 *
	 * @since 3.6
	 *
	 * @param  int    $post_id   The post ID.
	 * @param  string $post_type The post type.
	 * @param  string $cpcss     The generated CPCSS.
	 * @return bool
	 */
	protected function save_post_cpcss( $post_id, $post_type, $cpcss ) {
		if ( ! rocket_direct_filesystem()->is_dir( $this->critical_css_path ) ) {
			rocket_mkdir_p( $this->critical_css_path );
		}

		$filepath = "{$this->critical_css_path}{$post_type}-{$post_id}.css";

		return rocket_put_content( $filepath, wp_strip_all_tags( $cpcss, true ) );
	}
}
