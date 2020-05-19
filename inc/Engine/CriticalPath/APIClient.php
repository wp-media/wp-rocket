<?php
namespace WP_Rocket\Engine\CriticalPath;

use WP_Error;
use stdClass;

class APIClient {

	/**
	 * Constant url for Critical Path API job.
	 */
	const API_URL = 'https://cpcss.wp-rocket.me/api/job/';

	/**
	 * Sends a generation request to the Critical Path API
	 *
	 * @since 3.6
	 *
	 * @param string $url The URL to send a CPCSS generation request for.
	 * @return array
	 */
	public function send_generation_request( $url ) {
		$response = wp_remote_post(
			self::API_URL,
			[
				// This filter is documented in inc/Engine/CriticalPath/CriticalCSSGeneration.php.
				'body' => apply_filters(
					'rocket_cpcss_job_request',
					[
						'url' => $url,
					]
				),
			]
		);

		return $this->prepare_response( $response, $url );
	}

	/**
	 * Prepare the response to be returned.
	 *
	 * @since 3.6
	 *
	 * @param array|WP_Error $response The response or WP_Error on failure.
	 * @param string         $url Url to be checked.
	 * @return array|WP_Error
	 */
	private function prepare_response( $response, $url ) {
		$response_data        = $this->get_response_data( $response );
		$response_status_code = $this->get_response_status( $response, ( isset( $response_data->status ) ) ? $response_data->status : null );
		$succeeded            = $this->get_response_success( $response_status_code, $response_data );

		if ( $succeeded ) {
			return $response_data;
		}

		$response_message = $this->get_response_message( $response_status_code, $response_data, $url );

		if ( 200 === $response_status_code ) {
			$response_status_code = 400;
		}

		return new WP_Error(
			$this->get_response_code( $response ),
			$response_message,
			[
				'status' => $response_status_code,
			]
		);
	}

	/**
	 * Get the status of response.
	 *
	 * @since 3.6
	 *
	 * @param int      $response_code Response code to check success or failure.
	 * @param stdClass $response_data Object of data returned from request.
	 * @return bool success or failed.
	 */
	private function get_response_success( $response_code, $response_data ) {
		return (
			200 === $response_code
			&&
			! empty( $response_data )
			&&
			(
				(
					isset( $response_data->status )
					&&
					200 === $response_data->status
				)
				||
				(
					isset( $response_data->data )
					&&
					isset( $response_data->data->id )
				)
			)
		);
	}

	/**
	 * Get response status code/number.
	 *
	 * @since 3.6
	 *
	 * @param array|WP_Error $response The response or WP_Error on failure.
	 * @param null|int       $status Status code to overwrite the response status.
	 * @return int status code|number of response.
	 */
	private function get_response_status( $response, $status = null ) {
		if ( ! is_null( $status ) ) {
			return (int) $status;
		}

		return (int) wp_remote_retrieve_response_code( $response );
	}

	/**
	 * Get response message.
	 *
	 * @since 3.6
	 *
	 * @param int      $response_status_code Response status code.
	 * @param stdClass $response_data Object of data returned from request.
	 * @param string   $url Url for the web page to be checked.
	 * @return string
	 */
	private function get_response_message( $response_status_code, $response_data, $url ) {
		$message = '';

		switch ( $response_status_code ) {
			case 200:
				if ( ! isset( $response_data->data->id ) ) {
					$message .= sprintf(
					// translators: %s = item URL.
						__( 'Critical CSS for %1$s not generated. Error: The API returned an empty response.', 'rocket' ),
						$url
					);
				}
				break;
			case 400:
			case 440:
			case 404:
				// translators: %s = item URL.
				$message .= sprintf( __( 'Critical CSS for %1$s not generated.', 'rocket' ), $url );
				break;
			default:
				$message .= sprintf(
				// translators: %s = URL.
					__( 'Critical CSS for %1$s not generated. Error: The API returned an invalid response code.', 'rocket' ),
					$url
				);
				break;
		}

		if ( isset( $response_data->message ) ) {
			// translators: %1$s = error message.
			$message .= ' ' . sprintf( __( 'Error: %1$s', 'rocket' ), $response_data->message );
		}

		return $message;
	}

	/**
	 * Get response data from the API.
	 *
	 * @since 3.6
	 *
	 * @param array|WP_Error $response The response or WP_Error on failure.
	 * @return mixed response of API.
	 */
	private function get_response_data( $response ) {
		return json_decode( wp_remote_retrieve_body( $response ) );
	}

	/**
	 * Get our internal response code [Not the standard HTTP codes].
	 *
	 * @since 3.6
	 *
	 * @param array|WP_Error $response The response or WP_Error on failure.
	 * @return string response code.
	 */
	private function get_response_code( $response ) {
		// Todo: we can return code based on the response status number, for example 404 not_found.
		return 'cpcss_generation_failed';
	}

	/**
	 * Get job details by calling API with job ID.
	 *
	 * @since 3.6
	 *
	 * @param string $job_id ID for the job to get details.
	 * @param string $url URL to be used in error messages.
	 * @return mixed|WP_Error Details for job.
	 */
	public function get_job_details( $job_id, $url ) {
		$response = wp_remote_get(
			self::API_URL . "{$job_id}/"
		);

		return $this->prepare_response( $response, $url );
	}

}
