<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\StudioPress;

use Mockery;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\StudioPress::clean_accelerator_cache
 *
 * @group  StudioPress
 * @group  ThirdParty
 */
class Test_CleanAcceleratorCache extends TestCase {

	public function tear_down() {
		unset( $GLOBALS['sp_accel_nginx_proxy_cache_purge'] );

		parent::tear_down();
	}

	public function testShouldCallCacheFlushThemeWhenAcceleratorPresent() {
		$accelerator = Mockery::mock( 'SP_Accel_Nginx_Proxy_Cache_Purge' );
		$accelerator->expects()->cache_flush_theme();

		$GLOBALS['sp_accel_nginx_proxy_cache_purge'] = $accelerator;

		do_action( 'rocket_after_clean_domain' );
	}

	public function testShouldDoNothingWhenAcceleratorAbsent() {
		do_action( 'rocket_after_clean_domain' );

		$this->assertArrayNotHasKey( 'sp_accel_nginx_proxy_cache_purge', $GLOBALS );
	}
}
