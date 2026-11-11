<?php
declare(strict_types=1);

namespace WP_Rocket\Engine\Optimization\DynamicLists;

use WP_Error;
use WP_Rocket\Admin\Options_Data;

abstract class AbstractAPIClient {
	/**
	 * API URL.
	 */
	const API_URL = 'https://b.rucss.wp-rocket.me/api/v2/';

	/**
	 * Response Code.
	 *
	 * @var int
	 */
	private $response_code = 200;

	/**
	 * Error message.
	 *
	 * @var string
	 */
	private $error_message = '';

	/**
	 * Response Body.
	 *
	 * @var string
	 */
	private $response_body = '';

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
	 * Specify API endpoint path.
	 *
	 * @return string
	 */
	abstract protected function get_api_path();

	/**
	 * Get exclusions list.
	 *
	 * @param string $hash Hash of lists content to compare.
	 *
	 * @return array
	 */
	public function get_exclusions_list( $hash ) {
		$args = [
			'body'    => [
				'hash' => $hash,
			],
			'timeout' => 5,
		];

		if ( ! $this->handle_request( $args ) ) {
			return [
				'code'    => $this->response_code,
				'message' => $this->error_message,
			];
		}

		return [
			'code' => $this->response_code,
			'body' => $this->response_body,
		];
	}

	/**
	 * Handle the request.
	 *
	 * @param array $args Passed arguments.
	 *
	 * @return bool
	 */
	private function handle_request( array $args ) {
		$use_old_api_url = false;

		// Handle logic to set API Url to old version when rolling back to versions < 3.18.
		if ( version_compare( rocket_get_constant( 'WP_ROCKET_LASTVERSION' ), '3.18', '<' ) && 'rocket_before_rollback' === current_action() ) {
			$use_old_api_url = true;
		}

		$api_url = rocket_get_constant( 'WP_ROCKET_EXCLUSIONS_API_URL', false )
			? rocket_get_constant( 'WP_ROCKET_EXCLUSIONS_API_URL', false )
			: self::API_URL;

		$api_url = $use_old_api_url ? str_replace( 'v2/', '', $api_url ) : $api_url;

		if ( empty( $args['body'] ) ) {
			$args['body'] = [];
		}

		$args['body']['credentials'] = [
			'wpr_email' => $this->options->get( 'consumer_email', '' ),
			'wpr_key'   => $this->options->get( 'consumer_key', '' ),
		];

		$response = wp_remote_get(
			$api_url . $this->get_api_path(),
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

		if ( 200 !== $this->response_code && 206 !== $this->response_code ) {
			$this->error_message = is_array( $response )
				? wp_remote_retrieve_response_message( $response )
				: $response->get_error_message();

			return false;
		}

		$this->response_body = wp_remote_retrieve_body( $response );

		return true;
	}
}
