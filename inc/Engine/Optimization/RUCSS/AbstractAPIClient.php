<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS;

use WP_Error;

abstract class AbstractAPIClient {
	/**
	 * API URL.
	 */
	const API_URL = 'https://central-saas.wp-rocket.me/';

	/**
	 * Part of request Url after the main API_URL.
	 *
	 * @var string
	 */
	protected $request_path;

	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $error_message = '';

	/**
	 * Response Body.
	 *
	 * @var string
	 */
	protected $response_body;

	/**
	 * Handle remote POST.
	 *
	 * @param array $args Array with options sent to Saas API.
	 *
	 * @return bool WP Remote request status.
	 */
	protected function handle_post( array $args ) {
		$response = wp_remote_post(
			self::API_URL . $this->request_path,
			$args
		);

		return $this->check_response( $response );
	}

	/**
	 * Handle Saas request error.
	 *
	 * @param  array|WP_Error $response WP Remote request.
	 *
	 * @return bool
	 */
	private function check_response( $response ) {
		if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
			$this->error_message = wp_remote_retrieve_response_message( $response );

			return false;
		}

		$this->response_body = wp_remote_retrieve_body( $response );

		return true;
	}
}
