<?php

namespace WP_Rocket\Tests\Integration;

use WPMedia\PHPUnit\Integration\RESTfulTestCase as WPMediaRESTfulTestCase;

abstract class ApiTestCase extends WPMediaRESTfulTestCase {
	protected static $api_credentials_config_file = 'rocketcdn.php';

	/**
	 * Runs the RESTful endpoint which invokes WordPress to run in an integrated fashion. Callback will be fired.
	 *
	 * @param array $body_params Optional. Array of body parameters.
	 *
	 * @return array a response packet.
	 */
	protected function requestDisableEndpoint( array $body_params = [] ) {
		if ( empty( $body_params ) ) {
			$body_params = [
				'email' => '',
				'key'   => '',
			];
		}

		return $this->doRestPut( '/wp-rocket/v1/rocketcdn/disable', $body_params );
	}

	/**
	 * Runs the RESTful endpoint which invokes WordPress to run in an integrated fashion. Callback will be fired.
	 *
	 * @param array $body_params Optional. Array of body parameters.
	 *
	 * @return array a response packet.
	 */
	protected function requestEnableEndpoint( array $body_params = [] ) {
		if ( empty( $body_params ) ) {
			$body_params = [
				'email' => '',
				'key'   => '',
				'url'   => 'https://rocketcdn.me',
			];
		}

		return $this->doRestPut( '/wp-rocket/v1/rocketcdn/enable', $body_params );
	}
}
