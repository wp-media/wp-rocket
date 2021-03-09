<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\RUCSS;

class APIClient {
	/**
	 * API URL.
	 */
	const API_URL = 'https://central-saas.wp-rocket.me:30443/';

	/**
	 * SAAS main API path.
	 */
	const API_TREESHAKE = 'api';

	/**
	 * API Warmup path.
	 */
	const API_WARMUP = 'warmup';

	/**
	 * Calls Central Saas API.
	 *
	 * @param string $html    HTML content.
	 * @param string $url     HTML url.
	 * @param array  $options Array with options sent to Saas API.
	 *
	 * @return array
	 */
	public function optimize( string $html, string $url, array $options ) : array {
		$args = [
			'body' => [
				'html'   => $html,
				'url'    => $url,
				'config' => $options,
			],
			'timeout' => 30,
		];

		$request = wp_remote_post(
			self::API_URL . self::API_TREESHAKE,
			$args
		);

		$error_request = $this->handle_request_error( $request );
		if ( ! empty( $error_request ) ) {
			return $error_request;
		}

		$result = json_decode( wp_remote_retrieve_body( $request ), true );

		return [
			'code'            => $result['code'],
			'message'         => $result['message'],
			'css'             => $result['contents']['shakedCSS'],
			'unprocessed_css' => ( is_array( $result['contents']['unProcessedCss'] ) ? $result['contents']['unProcessedCss'] : [] ),
		];
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

		if ( 200 !== wp_remote_retrieve_response_code( $request ) ) {
			return [
				'code'    => wp_remote_retrieve_response_code( $request ),
				'message' => __( 'RUCSS is not available at the moment. Please retry later', 'rocket' ),
			];
		}

		return false;
	}
}
