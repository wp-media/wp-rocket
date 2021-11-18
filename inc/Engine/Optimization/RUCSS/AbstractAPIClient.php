<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\RUCSS;

use WP_Error;
use WP_Rocket\Admin\Options_Data;

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
	private $options;

	/**
	 * Instantiate the class.
	 *
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Options_Data $options ) {
		$this->options = $options;
	}

	/**
	 * Handle remote POST.
	 *
	 * @param array $args Array with options sent to Saas API.
	 *
	 * @return bool WP Remote request status.
	 */
	protected function handle_post( array $args ): bool {
		$api_url = rocket_get_constant( 'WP_ROCKET_SAAS_API_URL', false )
			? rocket_get_constant( 'WP_ROCKET_SAAS_API_URL', false )
			: self::API_URL;

		if ( empty( $args['body'] ) ) {
			$args['body'] = [];
		}

		$args['body']['credentials'] = [
			'wpr_email' => $this->options->get( 'consumer_email', '' ),
			'wpr_key'   => $this->options->get( 'consumer_key', '' ),
		];

		$response = wp_remote_post(
			$api_url . $this->request_path,
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
}
