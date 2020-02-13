<?php

namespace WP_Rocket\Tests\Integration;

use WP_Rest_Request;
use WP_REST_Server;
use WPMedia\PHPUnit\Integration\TestCase as WPMediaTestCase;

abstract class RESTfulTestCase extends WPMediaTestCase {
	protected $server;
	/**
	 * Name of the API credentials config file, if applicable. Set in the test or new TestCase.
	 *
	 * For example: rocketcdn.php or cloudflare.php.
	 *
	 * @var string
	 */
	protected static $api_credentials_config_file;

	/**
	 * Setup the WP REST API Server.
	 */
	public function setUp() {
		parent::setUp();
		/**
		 * @var WP_REST_Server $wp_rest_server
		 */
		global $wp_rest_server;
		$this->server = $wp_rest_server = new WP_REST_Server;
		do_action( 'rest_api_init' );
	}

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

	protected function doRestRequest( array $body_params, $route ) {
		$request = new WP_Rest_Request( 'PUT', $route );
		$request->set_header( 'Content-Type', 'application/x-www-form-urlencoded' );
		$request->set_body_params( $body_params );

		return rest_do_request( $request )->get_data();
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

		if ( ! self::$api_credentials_config_file ) {
			return '';
		}

		$config_file = dirname( __DIR__ ) . '/env/local/' . self::$api_credentials_config_file;

		if ( ! is_readable( $config_file ) ) {
			return '';
		}

		// This file is local to the developer's machine and not stored in the repo.
		require_once $config_file;

		return rocket_get_constant( $name, '' );
	}
}
