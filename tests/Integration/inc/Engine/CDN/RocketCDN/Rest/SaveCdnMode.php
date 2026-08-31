<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Rest;

use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Rest::save_cdn_mode
 * @group  RocketCDN
 * @group AdminOnly
 */
class Test_SaveCdnMode extends RESTfulTestCase {
	use CapTrait, DBTrait;

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
		self::setAdminCap();
		$this->admin_id = $this->factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $this->admin_id );

		$container          = apply_filters( 'rocket_container', null );
		$this->options_data = $container->get( 'options' );
		$this->options_api  = $container->get( 'options_api' );

		// save_cdn_mode() forces RocketCDN (free or paid) off unless there's an active
		// subscription and the CDN isn't paused; simulate both so activation isn't
		// rejected (matches the pattern used in AddPage.php).
		set_transient(
			'rocketcdn_status',
			[
				'subscription_status' => 'running',
				'cdn_url'              => 'example1.org',
			],
			HOUR_IN_SECONDS
		);

		// render_controller's Options_Data instance is frozen well before this test runs,
		// so writing 'cdn' through options_api/options_data here wouldn't be seen by it.
		// pre_get_rocket_option_cdn is read live on every call (Options_Data::get()), so
		// use that instead to make is_cdn_paused() see the CDN as enabled.
		add_filter( 'pre_get_rocket_option_cdn', '__return_true', PHP_INT_MAX );
	}

	public function tear_down() {
		wp_set_current_user( 0 );

		remove_filter( 'pre_get_rocket_option_cdn', '__return_true', PHP_INT_MAX );

		$settings = $this->options_api->get( 'settings', [] );
		unset( $settings['cdn_state'], $settings['cdn'], $settings['cdn_type'] );
		$this->options_api->set( 'settings', $settings );

		delete_transient( 'rocketcdn_status' );

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
			'/wp-rocket/v1/rocketcdn/mode',
			$config['params']
		);

		$settings = $this->options_api->get( 'settings', [] );

		foreach ( $expected as $key => $value ) {
			switch ( $key ) {
				case 'cdn_state_response':
					$this->assertSame( $value, $response['applied_cdn_state'] );
					$this->assertSame( $config['params']['mode'], $settings['cdn_state'] ?? null );
					break;
				case 'cdn':
					$this->assertSame( $value, $settings['cdn'] ?? null );
					break;
				case 'cdn_type':
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
