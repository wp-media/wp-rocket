<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\StudioPress;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\StudioPress;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\StudioPress::clean_accelerator_cache
 *
 * @group  StudioPress
 * @group  ThirdParty
 */
class Test_CleanAcceleratorCache extends TestCase {

	/**
	 * @var StudioPress
	 */
	protected $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->subscriber = new StudioPress();
	}

	protected function tearDown(): void {
		unset( $GLOBALS['sp_accel_nginx_proxy_cache_purge'] );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config ) {
		if ( $config['global_set'] ) {
			$accelerator = Mockery::mock( 'SP_Accel_Nginx_Proxy_Cache_Purge' );
			$accelerator->expects()->cache_flush_theme();

			$GLOBALS['sp_accel_nginx_proxy_cache_purge'] = $accelerator;
		}

		$this->subscriber->clean_accelerator_cache();

		if ( ! $config['global_set'] ) {
			$this->addToAssertionCount( 1 );
		}
	}
}
