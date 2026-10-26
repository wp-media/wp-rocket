<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Support\Subscriber;

use WPMedia\PHPUnit\Integration\RESTfulTestCase as WPMediaRESTfulTestCase;
use WP_Rocket\Tests\StubTrait;
use WP_Rocket\Tests\TestCredentials;

/**
 * Test class covering \WP_Rocket\Engine\Support\Subscriber::register_support_route
 *
 * @group Support
 */
class Test_RegisterSupportRoute extends WPMediaRESTfulTestCase {
	use StubTrait;

	protected $config;
	private $wp_version;

	public function set_up() {
		parent::set_up();

		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		add_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
		add_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );

		$this->stubRocketGetConstant();

		global $wp_version;

		$this->wp_version = $wp_version;
	}

	public function tear_down() {
		global $wp_version;

		$wp_version = $this->wp_version;

		remove_filter( 'pre_get_rocket_option_consumer_key', [ $this, 'set_consumer_key' ] );
		remove_filter( 'pre_get_rocket_option_consumer_email', [ $this, 'set_consumer_email' ] );

		$this->resetStubProperties();

		parent::tear_down();
	}

	public function testShouldRegisterRoute() {
		$this->assertArrayHasKey( '/wp-rocket/v1/support', $this->server->get_routes() );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $params, $expected ) {
		global $wp_version;

		$this->rocket_version = '3.7.5';
		$wp_version           = '5.5';

		$body = [
			'email' => $params['email'] ? TestCredentials::EMAIL : '',
			'key'   => $params['key'] ? TestCredentials::KEY : '',
		];

		$actual = $this->requestSupportEndpoint( $body );

		foreach ( $expected as $key => $value ) {
			$this->assertArrayHasKey( $key, $actual );

			if ( is_array( $value ) ) {
				foreach ( $value as $sub_key => $sub_value ) {
					$this->assertArrayHasKey( $sub_key, $actual[ $key ] );
					$this->assertSame( $sub_value, $actual[ $key ][ $sub_key ] );
				}
			} else {
				$this->assertSame( $value, $actual[ $key] );
			}
		}
	}

	protected function requestSupportEndpoint(  array $body_params = [] ) {
		if ( empty( $body_params ) ) {
			$body_params = [
				'email' => '',
				'key'   => '',
			];
		}

		return $this->doRestRequest( 'POST', '/wp-rocket/v1/support', $body_params );
	}

	public function set_consumer_key() {
		return TestCredentials::KEY;
	}

	public function set_consumer_email() {
		return TestCredentials::EMAIL;
	}

	public function configTestData() {
		if ( empty( $this->config ) ) {
			$this->loadTestDataConfig();
		}

		return isset( $this->config['test_data'] )
			? $this->config['test_data']
			: $this->config;
	}

	protected function loadTestDataConfig() {
		$obj      = new \ReflectionObject( $this );
		$filename = $obj->getFileName();

		$this->config = $this->getTestData( dirname( $filename ), basename( $filename, '.php' ) );
	}
}
