<?php

namespace WP_Rocket\Engine\CriticalPath;

use WP_REST_Request;
use WP_REST_Response;
use WP_Error;

/**
 * Class RESTWP
 *
 * @package WP_Rocket\Engine\CriticalPath
 */
abstract class RESTWP {

	/**
	 * Namespace for REST Route.
	 */
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Part of route namespace for this inherited class item type.
	 *
	 * @var string $route_namespace to be set with like post, term.
	 */
	protected $route_namespace;

	/**
	 * Responsible for dealing with data/database.
	 *
	 * @var DataManager datamanager instance.
	 */
	private $data_manager;

	/**
	 * Responsible for dealing with CPCSS APIs.
	 *
	 * @var APIClient api_client instance.
	 */
	private $api_client;

	/**
	 * RESTWP constructor.
	 *
	 * @since 3.6
	 *
	 * @param DataManager $data_manager Data manager instance, responsible for dealing with data/database.
	 * @param APIClient   $api_client API Client instance to deal with CPCSS APIs.
	 */
	public function __construct( DataManager $data_manager, APIClient $api_client ) {
		$this->data_manager = $data_manager;
		$this->api_client   = $api_client;
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
			'cpcss/' . $this->route_namespace . '/(?P<id>[\d]+)',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'generate' ],
				'permission_callback' => [ $this, 'check_permissions' ],
			]
		);
	}

	/**
	 * Register Delete CPCSS route in the WP REST API.
	 *
	 * @since  3.6
	 */
	public function register_delete_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'cpcss/' . $this->route_namespace . '/(?P<id>[\d]+)',
			[
				'methods'             => 'DELETE',
				'callback'            => [ $this, 'delete' ],
				'permission_callback' => [ $this, 'check_permissions' ],
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

	/**
	 * Generates the CPCSS for the requested post ID.
	 *
	 * @since 3.6
	 *
	 * @param  WP_REST_Request $request WP REST request response.
	 * @return WP_REST_Response
	 */
	public function generate( WP_REST_Request $request ) {
		$item_id = intval( $request['id'] );
		$output  = null;

		// validate item.
		$validated = $this->validate_item( $item_id );
		if ( ! is_wp_error( $validated ) ) {
			// get item url.
			$item_url = $this->get_url( $item_id );
			$timeout  = ( isset( $request['timeout'] ) && ! empty( $request['timeout'] ) );

			$output = $this->process_generate( $item_url, $item_id, $timeout );
		}else {
			$output = $this->return_error( $validated );
		}

		return rest_ensure_response( $output );

	}

	/**
	 * Process CPCSS generation, Check timeout and send the generation request.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url URL for item to be used in error messages.
	 * @param int    $item_id ID for item to be processed.
	 * @param bool   $timeout Timeout is requested or not.
	 * @return array
	 */
	private function process_generate( $item_url, $item_id, $timeout = false ) {
		// Ajax call requested a timeout.
		if ( $timeout ) {
			return $this->process_timeout( $item_url );
		}

		$cpcss_job_id = $this->data_manager->get_cache_job_id( $item_url );

		if ( false === $cpcss_job_id ) {
			return $this->send_generation_request( $item_url, $item_id );
		}

		// job_id is found and we need to check status for it.
		return $this->check_cpcss_job_status( $cpcss_job_id, $item_id, $item_url );
	}

	/**
	 * Send Generation first request.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url Url for item to send the generation request for.
	 * @param int    $item_id ID for item to send the generation request for.
	 * @return array
	 */
	private function send_generation_request( $item_url, $item_id ) {
		$output = null;

		// call send generation request from APIClient for the first time.
		$generated_job = $this->api_client->send_generation_request( $item_url );

		// validate generate response.
		if ( is_wp_error( $generated_job ) ) {
			// Failed so return back the data.
			return $this->return_error( $generated_job );
		}

		// Send generation request succeeded.
		// Save job_id into cache.
		$this->data_manager->set_cache_job_id( $item_url, $generated_job->data->id );

		return $this->check_cpcss_job_status( $generated_job->data->id, $item_id, $item_url );
	}

	/**
	 * Get job details by job_id.
	 *
	 * @since 3.6
	 *
	 * @param string $job_id ID for the job to get details.
	 * @param string $item_url URL for item to be used in error messages.
	 * @return array|mixed|WP_Error
	 */
	private function get_cpcss_job_details( $job_id, $item_url ) {
		$job_details = $this->api_client->get_job_details( $job_id, $item_url );

		if ( is_wp_error( $job_details ) ) {
			return $this->return_error( $job_details );
		}else {
			return $job_details;
		}
	}

	/**
	 * Check status and process the output for a job.
	 *
	 * @since 3.6
	 *
	 * @param string $job_id ID for the job to get details.
	 * @param int    $item_id ID for this item to be validated.
	 * @param string $item_url URL for item to be used in error messages.
	 * @return array Response in case of success, failure or pending.
	 */
	private function check_cpcss_job_status( $job_id, $item_id, $item_url ) {
		$job_details = $this->api_client->get_job_details( $job_id, $item_url );

		if ( is_wp_error( $job_details ) ) {
			return $this->return_error( $job_details );
		}else {
			if ( 200 !== $job_details->status ) {
				// On job error.
				$this->data_manager->delete_cache_job_id( $item_url );

				// translators: %1$s = page URL.
				$error = sprintf( __( 'Critical CSS for %1$s not generated.', 'rocket' ), $item_url );

				if ( isset( $job_data->message ) ) {
					// translators: %1$s = error message.
					$error .= ' ' . sprintf( __( 'Error: %1$s', 'rocket' ), $job_data->message );
				}

				return $this->return_error(
					new WP_Error(
						'cpcss_generation_failed',
						$error,
						[
							'status' => 400,
						]
					)
				);
			}else {
				// On job status 200.
				if ( isset( $job_details->data->state ) && 'complete' !== $job_details->data->state ) {
					return $this->return_success(
						'cpcss_generation_pending',
						// translators: %s = post URL.
						sprintf( __( 'Critical CSS for %s in progress.', 'rocket' ), $item_url )
					);
				}

				if ( isset( $job_details->data->state, $job_details->data->critical_path ) && 'complete' === $job_details->data->state ) {

					// delete cache job_id for this item.
					$this->data_manager->delete_cache_job_id( $item_url );

					// save the generated CPCSS code into file.
					$this->data_manager->save_cpcss( $this->get_path( $item_id ), $job_details->data->critical_path );

					// Send the current status of job.
					return $this->return_success(
						'cpcss_generation_successful',
						// translators: %s = post URL.
						sprintf( __( 'Critical CSS for %s generated.', 'rocket' ), $item_url )
					);
				}
			}
		}

	}

	/**
	 * Validate the item to be sent to generate CPCSS.
	 *
	 * @since 3.6
	 *
	 * @param int $item_id ID for this item to be validated.
	 * @return true|WP_Error
	 */
	abstract protected function validate_item( $item_id );

	/**
	 * Get url for this item.
	 *
	 * @since 3.6
	 *
	 * @param int $item_id ID for this item to get Url for.
	 * @return false|string
	 */
	abstract protected function get_url( $item_id );

	/**
	 * Get CPCSS file path to save CPCSS code into.
	 *
	 * @since 3.6
	 *
	 * @param int $item_id ID for this item to get the path for.
	 * @return string
	 */
	abstract protected function get_path( $item_id );

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
		$item_id = intval( $request['id'] );
		$output  = null;

		// validate item.
		$validated = $this->validate_item( $item_id );
		if ( ! is_wp_error( $validated ) ) {

			$deleted = $this->data_manager->delete_cpcss( $this->get_path( $item_id ) );

			if ( is_wp_error( $deleted ) ) {
				$output = $this->return_error( $deleted );
			}else {
				$output = $this->return_success(
					'success',
					__( 'Critical CSS file deleted successfully.', 'rocket' )
				);
			}
		}else {
			$output = $this->return_error( $validated );
		}

		return rest_ensure_response( $output );
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
			'message' => $message,
			'data'    => [
				'status' => $status,
			],
		];
	}

	/**
	 * Convert WP_Error into array to be used in response.
	 *
	 * @since 3.6
	 *
	 * @param WP_Error $error Error that will be converted to array.
	 * @return array
	 */
	protected function return_error( WP_Error $error ) {
		$error_data = $error->get_error_data();

		return $this->return_array_response(
			false,
			$error->get_error_code(),
			$error->get_error_message(),
			isset( $error_data['status'] ) ? $error_data['status'] : 400
		);
	}

	/**
	 * Return success to be used in response.
	 *
	 * @since 3.6
	 *
	 * @param string $code Code represents the status.
	 * @param string $message Message to be sent.
	 * @return array
	 */
	protected function return_success( $code, $message = '' ) {
		return $this->return_array_response(
			true,
			$code,
			$message,
			200
		);
	}

	/**
	 * Process timeout action for CPCSS generation.
	 *
	 * @since 3.6
	 *
	 * @param string $item_url URL for item to be used in error messages.
	 * @return array
	 */
	private function process_timeout( $item_url ) {
		$this->data_manager->delete_cache_job_id( $item_url );

		return $this->return_error(
			new WP_Error(
			'cpcss_generation_timeout',
			// translators: %1$s = Item URL.
			sprintf( __( 'Critical CSS for %1$s timeout. Please retry a little later.', 'rocket' ), $item_url ),
			[
				'status' => 400,
			]
		)
			);
	}

}
