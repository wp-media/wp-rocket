<?php
declare( strict_types=1 );

namespace WP_Rocket\Engine\Common\JobManager\APIHandler;

use WP_Error;
use WP_Rocket\Admin\Options_Data;

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

		if ( ! empty( $args['json_encode'] ) ) {
			unset( $args['json_encode'] );
			$args['body'] = wp_json_encode( $args['body'] );
		}

		$response = wp_remote_request(
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
	protected function check_response( $response ): bool {
		$this->response_code = is_array( $response )
			? wp_remote_retrieve_response_code( $response )
			: $response->get_error_code();

		if ( ! in_array( (int) $this->response_code, [ 200, 201 ], true ) ) {
			if ( empty( $response ) ) {
				$this->error_message = 'API Client Error';
				return false;
			}

			$this->error_message = is_array( $response )
				? wp_remote_retrieve_response_message( $response )
				: $response->get_error_message();

			return false;
		}

		$this->response_body = wp_remote_retrieve_body( $response );

		return true;
	}

	/**
	 * Validate add to queue response.
	 *
	 * @param array $response Response array to be validated.
	 * @return bool
	 */
	abstract public function validate_add_to_queue_response( array $response ): bool;

	/**
	 * Add a filter on url.
	 *
	 * @param string $url Url to be filtered.
	 * @param string $optimization_type Optimization type.
	 *
	 * @return mixed
	 */
	protected function filter_url( $url, $optimization_type ) {
		/**
		 * Filter the url that is sent to Saas.
		 *
		 * @param string $url contains the URL that is sent to Saas.
		 * @param string $optimization_type Optimization type.
		 */
		return wpm_apply_filters_typed( 'string', 'rocket_saas_api_queued_url', $url, $optimization_type );
	}
}
