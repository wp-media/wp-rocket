<?php

namespace WP_Rocket\Engine\Support;

use WP_Rocket\Admin\Options_Data;

class Rest {
	const ROUTE_NAMESPACE = 'wp-rocket/v1';

	private $data;
	private $options;

	public function __construct( Data $data, Options_Data $options ) {
		$this->data    = $data;
		$this->options = $options;
	}

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

	public function get_support_data() {
		if ( false === strpos( wp_get_referer(), 'wp-rocket.me' ) ) {
			return rest_ensure_response( [] );
		}

		return rest_ensure_response( $this->data->get_support_data() );
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
		return $param === $this->options->get( 'consumer_email' );
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
		return $param === $this->options->get( 'consumer_key' );
	}
}
