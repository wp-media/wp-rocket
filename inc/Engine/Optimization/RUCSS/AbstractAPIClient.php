<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS;

abstract class AbstractAPIClient {
	/**
	 * API URL.
	 */
	const API_URL = 'https://central-saas.wp-rocket.me/';

	/**
	 * Handle remote POST.
	 *
	 * @param string $path API path.
	 * @param array  $args Array with options sent to Saas API.
	 *
	 * @return array|WP_Error $request WP Remote request.
	 */
	protected function handle_post( string $path, array $args ) {
		$request = wp_remote_post(
			self::API_URL . $path,
			$args
		);

		return $request;
	}

	/**
	 * Handle Saas request error.
	 *
	 * @param  array|WP_Error $request WP Remote request.
	 *
	 * @return array
	 */
	protected function handle_request_error( $request ) {
		if ( is_wp_error( $request ) ) {
			return [
				'code'    => 400,
				'message' => $request->get_error_message(),
			];
		}

		$code = wp_remote_retrieve_response_code( $request );

		if ( 200 !== $code ) {
			return [
				'code'    => $code,
				'message' => __( 'Remove Unused CSS is not available at the moment. Please retry later', 'rocket' ),
			];
		}

		return false;
	}
}
