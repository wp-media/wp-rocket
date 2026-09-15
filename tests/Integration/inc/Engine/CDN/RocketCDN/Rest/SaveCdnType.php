<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Rest;

use WP_Rocket\Tests\Integration\ApiTestCase;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase;
use WP_Rocket\Tests\Integration\IsolateHookTrait;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Rest::save_cdn_type
 * @group  RocketCDN
 * @group AdminOnly
 */
class Test_SaveCdnType extends RESTfulTestCase {
	use CapTrait, DBTrait, IsolateHookTrait;

	private $admin_id;

	protected $config;

	protected $options_data;
	protected $options_api;

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installRocketCDNTable();
	}

	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		// Enabling the CDN updates the settings, firing every `update_option_wp_rocket_settings`
		// subscriber. Some of them query tables that check_status() never touches (e.g. the
		// Preconnect External Domains subscriber truncates its table). Isolate the whole hook so
		// no current or future incidental subscriber leaks a side effect into this test.
		$this->unregisterAllCallbacks( 'update_option_wp_rocket_settings' );
		self::setAdminCap();
		$this->admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->admin_id );

		$container          = apply_filters( 'rocket_container', null );
		$this->options_data = $container->get( 'options' );
		$this->options_api  = $container->get( 'options_api' );
	}

	public function tear_down() {
		wp_set_current_user( 0 );

		$settings = $this->options_api->get( 'settings', [] );
		unset( $settings['cdn_type'] );
		$this->options_api->set( 'settings', $settings );

		parent::tear_down();
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

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		if ( ! empty( $config['unauthenticated'] ) ) {
			wp_set_current_user( 0 );
		}

		$response = $this->doRestRequest(
			'POST',
			'/wp-rocket/v1/rocketcdn/driver',
			$config['params']
		);

		foreach ( $expected as $key => $value ) {
			switch ( $key ) {
				case 'cdn_type_response':
					$settings = $this->options_api->get( 'settings', [] );
					$this->assertSame( $value, $response['cdn_type'] );
					$this->assertSame( $value, $settings['cdn_type'] ?? null );
					break;
				case 'code':
					$this->assertSame( $value, $response['code'] );
					break;
				case 'status':
					$this->assertSame( $value, $response['data']['status'] );
					break;
			}
		}
	}
}
