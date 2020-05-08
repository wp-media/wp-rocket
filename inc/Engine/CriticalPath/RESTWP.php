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
	 * RESTWP constructor.
	 *
	 * @since 3.6
	 *
	 * @param DataManager $data_manager datamanager instance, responsible for dealing with data/database.
	 */
	public function __construct( DataManager $data_manager ) {
		$this->data_manager = $data_manager;
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

			// Ajax call requested a timeout.
			$request_timeout = $request['timeout'];
			if ( ! empty( $request_timeout ) ) {
				$output = $this->process_timeout( $item_url );
			}else {
				$cpcss_job_id = $this->data_manager->get_cache_job_id( $item_url );

				if ( false === $cpcss_job_id ) {
					// call send generation request from APIClient through DM for the first time.
					$generated_job = $this->data_manager->send_generation_request( $item_url );

					// validate generate response.
					if ( is_wp_error( $generated_job ) ) {
						// Failed so return back the data.
						$output = $this->return_error( $generated_job );
					}else {
						// Send generation request succeeded.
						// Save job_id into cache.
						$this->data_manager->set_cache_job_id( $item_url, $generated_job->data->id );

						// delete cache job_id for this item.
						$this->data_manager->delete_cache_job_id( $item_url );

						// save the generated CPCSS code into file.
						$this->data_manager->save_cpcss( $this->get_path( $item_id ), $generated_job->data->critical_path );

						// Send the current status of job.
						$output = $this->get_cpcss_job_details( $cpcss_job_id, $item_url );
					}
				}else {
					// job_id is found and we need to check status for it.
					$output = $this->get_cpcss_job_details( $cpcss_job_id, $item_url );

				}
			}
		}else {
			$output = $this->return_error( $validated );
		}

		return rest_ensure_response( $output );

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
		$job_details = $this->data_manager->get_cpcss_job_details( $job_id, $item_url );

		if ( is_wp_error( $job_details ) ) {
			return $this->return_error( $job_details );
		}else {
			return $job_details;
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
	 * @param int $item_id ID for this item to be validated.
	 * @return false|string
	 */
	abstract protected function get_url( $item_id );

	/**
	 * Get CPCSS file path to save CPCSS code into.
	 *
	 * @since 3.6
	 *
	 * @param int $item_id ID for this item to be validated.
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
				'status' => 408,
			]
		)
			);
	}

}
