<?php

namespace WP_Rocket\Tests\Integration;

use WPMedia\PHPUnit\Integration\RESTfulTestCase as WPMediaRESTfulTestCase;

abstract class ApiTestCase extends WPMediaRESTfulTestCase {

	/**
	 * Name of the API credentials config file, if applicable. Set in the test or new TestCase.
	 *
	 * For example: rocketcdn.php or cloudflare.php.
	 *
	 * @var string
	 */
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

		return $this->doRestRequest( $body_params, '/wp-rocket/v1/rocketcdn/disable' );
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

		return $this->doRestRequest( $body_params, '/wp-rocket/v1/rocketcdn/enable' );
	}


	/**
	 * Gets the credential's value from either an environment variable (stored locally on the machine or CI) or from a local constant defined in `tests/env/local/cloudflare.php`.
	 *
	 * @param string $name Name of the environment variable or constant to find.
	 *
	 * @return string returns the value if available; else an empty string.
	 */
	protected static function getApiCredential( $name ) {
		$var = getenv( $name );
		if ( ! empty( $var ) ) {
			return $var;
		}

		if ( ! static::$api_credentials_config_file ) {
			return '';
		}

		$config_file = dirname( __DIR__ ) . '/env/local/' . static::$api_credentials_config_file;

		if ( ! is_readable( $config_file ) ) {
			return '';
		}

		// This file is local to the developer's machine and not stored in the repo.
		require_once $config_file;

		return rocket_get_constant( $name, '' );
	}
}
