<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Admin\Options_Data;

class Rest {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	/**
	 * Data instance
	 *
	 * @var Data
	 */
	private $data;

	/**
	 * Options instance
	 *
	 * @var Options_Data
	 */
	private $options;

	/**
	 * Instantiate the class
	 *
	 * @param Data         $data    Data instance.
	 * @param Options_Data $options Options instance.
	 */
	public function __construct( Data $data, Options_Data $options ) {
		$this->data    = $data;
		$this->options = $options;
	}

	/**
	 * Registers the REST route to get the support data
	 *
	 * @since 3.7.5
	 *
	 * @return void
	 */
	public function register_route() {
		register_rest_route(
			self::ROUTE_NAMESPACE,
			'support',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'get_support_data' ],
				'args'                => [
					'email' => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_email' ],
					],
					'key'   => [
						'required'          => true,
						'validate_callback' => [ $this, 'validate_key' ],
					],
				],
				'permission_callback' => '__return_true',
			]
		);
	}

	/**
	 * Returns the support data if the referer is correct
	 *
	 * @since 3.7.5
	 *
	 * @return WP_REST_Response
	 */
	public function get_support_data() {
		return rest_ensure_response(
			[
				'code'    => 'rest_support_data_success',
				'message' => 'Support data request successful',
				'data'    => [
					'status'  => 200,
					'content' => $this->data->get_support_data(),
				],
			]
		);
	}

	/**
	 * Checks that the email sent along the request corresponds to the one saved in the DB
	 *
	 * @since 3.7.5
	 *
	 * @param string $param Parameter value to validate.
	 *
	 * @return bool
	 */
	public function validate_email( $param ) {
		return ! empty( $param ) && $param === $this->options->get( 'consumer_email', '' );
	}

	/**
	 * Checks that the key sent along the request corresponds to the one saved in the DB
	 *
	 * @since 3.7.5
	 *
	 * @param string $param Parameter value to validate.
	 *
	 * @return bool
	 */
	public function validate_key( $param ) {
		return ! empty( $param ) && $param === $this->options->get( 'consumer_key', '' );
	}
}
