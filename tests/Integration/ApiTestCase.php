<?php

namespace WP_Rocket\Tests\Integration;

use WPMedia\PHPUnit\Integration\RESTfulTestCase as WPMediaRESTfulTestCase;

abstract class ApiTestCase extends WPMediaRESTfulTestCase {
	use DBTrait;

	protected static $api_credentials_config_file = 'rocketcdn.php';

	public static function set_up_before_class() {
		parent::set_up_before_class();

		self::uninstallAll();

		self::pathToApiCredentialsConfigFile( WP_ROCKET_TESTS_DIR . '/../env/local/' );
	}

	public static function tear_down_after_class()
	{
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_email' ] );
		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_key' ] );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_email' ] );
		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_key' ] );

		parent::tear_down();
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

	public function set_email() {
		return self::getApiCredential( 'ROCKET_EMAIL' );
	}

	public function set_key() {
		return self::getApiCredential( 'ROCKET_KEY' );
	}
}
