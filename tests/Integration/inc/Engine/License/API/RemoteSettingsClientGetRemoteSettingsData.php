<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Tests\StubTrait;

/**
 * Test class covering \WP_Rocket\Engine\License\API\RemoteSettingsClient::get_remote_settings_data
 *
 * @group License
 * @group AdminOnly
 */
class RemoteSettingsClientGetRemoteSettingsData extends TestCase {
	use StubTrait;
	private static $client;
	private $response;

	public static function set_up_before_class() {
		parent::set_up_before_class();

		$container     = apply_filters( 'rocket_container', null );
		self::$client  = $container->get( 'remote_settings_client' );
	}

	public function set_up() {
		$this->rocket_version = '3.22';
		parent::set_up();

		delete_transient( 'wp_rocket_remote_settings' );
		delete_transient( 'wp_rocket_remote_settings_timeout' );
		delete_transient( 'wp_rocket_remote_settings_timeout_active' );

		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
	}

	public function tear_down() {
		delete_transient( 'wp_rocket_remote_settings' );
		delete_transient( 'wp_rocket_remote_settings_timeout' );
		delete_transient( 'wp_rocket_remote_settings_timeout_active' );

		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );
		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
		remove_filter( 'pre_http_request', [ $this, 'set_response' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( false !== $config['transient'] ) {
			set_transient( 'wp_rocket_remote_settings', $config['transient'] );
		}

		if ( false !== $config['timeout-duration'] ) {
			set_transient( 'wp_rocket_remote_settings_timeout', $config['timeout-duration'], WEEK_IN_SECONDS );
		}

		if ( $config['timeout-active'] ) {
			set_transient( 'wp_rocket_remote_settings_timeout_active', true, WEEK_IN_SECONDS );
		}

		$this->response = $config['response'];

		add_filter( 'pre_http_request', [ $this, 'set_response' ] );

		$this->assertEquals(
			$expected,
			self::$client->get_remote_settings_data()
		);
	}

	public function set_consumer_email() {
		return 'test@example.com';
	}

	public function set_consumer_key() {
		return 'test_consumer_key';
	}

	public function set_response() {
		return $this->response;
	}
}
