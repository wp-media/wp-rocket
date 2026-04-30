<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\Rest;

use WP_Rocket\Tests\Integration\ApiTestCase;
use WP_Rocket\Tests\Integration\CapTrait;
use WP_Rocket\Tests\Integration\DBTrait;
use WPMedia\PHPUnit\Integration\RESTfulTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\Rest::save_state
 * @group  RocketCDN
 * @group AdminOnly
 */
class Test_SaveState extends RESTfulTestCase {
	use CapTrait, DBTrait;

	private $admin_id;

	private $config;

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
	}

	public function tear_down() {
		wp_set_current_user( 0 );
		delete_option( 'wpr_rocketcdn_active_driver' );
		delete_option( 'wpr_rocketcdn_paused' );
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
		// Pre-set options if configured.
		foreach ( $config['preset_options'] as $option => $value ) {
			update_option( $option, $value );
		}

		// Set unauthenticated if configured.
		if ( ! empty( $config['unauthenticated'] ) ) {
			wp_set_current_user( 0 );
		}

		$response = $this->doRestRequest( 'POST', '/wp-rocket/v1/rocketcdn/state', $config['params'] );

		foreach ( $expected as $key => $value ) {
			switch ( $key ) {
				case 'code':
					$this->assertSame( $value, $response['code'] );
					break;
				case 'active_driver_option':
					$this->assertSame( $value, get_option( 'wpr_rocketcdn_active_driver' ) );
					break;
				case 'paused_option':
					$this->assertSame( $value, get_option( 'wpr_rocketcdn_paused' ) );
					break;
			}
		}
	}
}
