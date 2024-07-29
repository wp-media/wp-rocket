<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Admin;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\CriticalPath\Admin\Admin;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\AdminTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\Admin\Admin::enqueue_admin_cpcss_heartbeat_script
 *
 * @group CriticalPath
 * @group CriticalPathAdmin
 */
class TestEnqueueAdminCpcssHeartbeatScript extends TestCase {
	use AdminTrait;

	private $admin;

	public function setUp(): void {
		parent::setUp();

		$this->setUpMocks();
		Functions\stubTranslationFunctions();

		$this->admin = new Admin(
			$this->options,
			Mockery::mock( ProcessorService::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldEnqueueAdminScript( $config, $expected ) {
		$this->options
				->shouldReceive( 'get' )
				->with( 'async_css', 0 )
				->andReturn( $config['options']['async_css'] );

		if ( $expected ) {
			$this->assertExpected();
		} else {
			$this->assertNotExpected();
		}

		$this->admin->enqueue_admin_cpcss_heartbeat_script();
	}

	private function assertExpected() {
		Functions\expect( 'wp_create_nonce' )
			->once()
			->with( 'cpcss_heartbeat_nonce' )
			->andReturn( 'wp_cpcss_heartbeat_nonce' );

		Functions\expect( 'wp_enqueue_script' )
			->once()
			->with(
				'wpr-heartbeat-cpcss-script',
				rocket_get_constant( 'WP_ROCKET_ASSETS_JS_URL' ) . 'wpr-cpcss-heartbeat.js',
				[],
				rocket_get_constant( 'WP_ROCKET_VERSION' ),
				true
			)
			->andReturnNull();

		Functions\expect( 'wp_localize_script' )
			->once()
			->with(
				'wpr-heartbeat-cpcss-script',
				'rocket_cpcss_heartbeat',
				[
					'nonce' => 'wp_cpcss_heartbeat_nonce',
				]
			)
			->andReturnNull();
	}

	private function assertNotExpected() {
		Functions\expect( 'wp_enqueue_script' )->never();
		Functions\expect( 'wp_create_nonce' )->never();
		Functions\expect( 'wp_localize_script' )->never();
	}
}
