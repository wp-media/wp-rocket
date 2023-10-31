<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Optimization\RUCSS;

use WP_Error;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Logger\Logger;

abstract class AbstractAPIClient {
	/**
	 * API URL.
	 */
	const API_URL = 'https://saas.wp-rocket.me/';

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
	 * @param array  $args Passed arguments.
	 * @param string $type GET or POST.
	 *
	 * @return bool
	 */
	private function handle_request( array $args, string $type = 'post' ) {
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

		$args['method'] = strtoupper( $type );
		$response       = wp_remote_request(
			$api_url . $this->request_path,
			$args
		);

		return $this->check_response( $response );
	}

	/**
	 * Handle remote POST.
	 *
	 * @param array $args Array with options sent to Saas API.
	 *
	 * @return bool WP Remote request status.
	 */
	protected function handle_post( array $args ): bool {
		return $this->handle_request( $args );
	}

	/**
	 * Handle remote GET.
	 *
	 * @param array $args Array with options sent to Saas API.
	 *
	 * @return bool WP Remote request status.
	 */
	protected function handle_get( array $args ): bool {
		return $this->handle_request( $args, 'get' );
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
			$previous_errors = (int) get_transient( 'wp_rocket_rucss_errors_count' );
			set_transient( 'wp_rocket_rucss_errors_count', $previous_errors + 1, 5 * MINUTE_IN_SECONDS );
			$this->error_message = is_array( $response )
				? wp_remote_retrieve_response_message( $response )
				: $response->get_error_message();

			return false;
		}
		delete_transient( 'wp_rocket_rucss_errors_count' );
		$this->response_body = wp_remote_retrieve_body( $response );

		return true;
	}
}
