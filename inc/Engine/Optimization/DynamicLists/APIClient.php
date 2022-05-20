<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Error;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Logger\Logger;

class APIClient {
	/**
	 * API URL.
	 */
	//const API_URL = 'https://b.rucss.wp-rocket.me/api/';
	const API_URL = 'http://127.0.0.1:8000/api/';


	/**
	 * Response Code.
	 *
	 * @var int
	 */
	protected $response_code = 200;

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
	 * Plugin options instance.
	 *
	 * @var Options_Data
	 */
	protected $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Handle the request.
	 *
	 * @param array $args Passed arguments.
	 * @param string $request_path request path.
	 * @param string $type GET or POST.
	 *
	 * @return bool
	 */
	private function handle_request( string $request_path, array $args, string $type = 'post' ) {
		$api_url = rocket_get_constant( 'WP_ROCKET_EXCLUSIONS_API_URL', false )
			? rocket_get_constant( 'WP_ROCKET_EXCLUSIONS_API_URL', false )
			: self::API_URL;

		if ( empty( $args['body'] ) ) {
			$args['body'] = [];
		}

		$args['body']['credentials'] = [
			'wpr_email' => $this->options->get( 'consumer_email', '' ),
			'wpr_key'   => $this->options->get( 'consumer_key', '' ),
		];

		$args['method'] = strtoupper( $type );

		$response = wp_remote_request(
			$api_url . $request_path,
			$args
		);

		return $this->check_response( $response );
	}

	/**
	 * Handle SaaS request error.
	 *
	 * @param array|WP_Error $response WP Remote request.
	 *
	 * @return bool
	 */
	private function check_response( $response ): bool {
		$this->response_code = is_array( $response )
			? wp_remote_retrieve_response_code( $response )
			: $response->get_error_code();

		if ( 200 !== $this->response_code ) {
			$this->error_message = is_array( $response )
				? wp_remote_retrieve_response_message( $response )
				: $response->get_error_message();

			return false;
		}

		$this->response_body = wp_remote_retrieve_body( $response );

		return true;
	}

	/**
	 * Get exclusions list.
	 *
	 * @return array
	 */
	public function get_exclusions_list( $hash ) {
		$args = [
			'body'    => [
				'hash' => $hash
			],
			'timeout' => 5,
		];

		if ( ! $this->handle_request( 'exclusions/list', $args, 'get' ) ) {
			return [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];
		}

		$result = [
			'code' => $this->response_code,
			'body' => $this->response_body,
		];

		return $result;

	}
}
