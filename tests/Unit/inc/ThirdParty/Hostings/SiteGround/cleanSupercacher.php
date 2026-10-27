<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\SiteGround;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SiteGround;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SiteGround::clean_supercacher
 *
 * SG Optimizer 5.0+ purges through the namespaced Supercacher class; earlier versions expose the
 * legacy $sg_cachepress_supercacher global. Nothing is purged when the supercacher is not active.
 * See issue #8768.
 *
 * @group SiteGround
 * @group ThirdParty
 * @group Hostings
 */
class Test_CleanSupercacher extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_PLUGIN_DIR' ) ) {
			define( 'WP_PLUGIN_DIR', 'vfs://plugins' );
		}
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldCallNamespacedSupercacherPurgeOnModernVersionWhenActive() {
		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '5.5' ] );
		Functions\when( 'get_option' )->justReturn( 1 );

		$purge_cache_spy = Mockery::mock( 'overload:SiteGround_Optimizer\Supercacher\Supercacher' );
		$purge_cache_spy->shouldReceive( 'purge_cache' )->once();

		( new SiteGround() )->clean_supercacher();
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldUseLegacyGlobalOnLegacyVersionWhenActive() {
		global $sg_cachepress_environment, $sg_cachepress_supercacher;

		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '4.9' ] );
		Functions\when( 'get_option' )->justReturn( 0 );

		$sg_cachepress_environment = Mockery::mock( 'overload:SG_CachePress_Environment' );
		$sg_cachepress_environment->shouldReceive( 'cache_is_enabled' )->andReturn( true );

		$sg_cachepress_supercacher = Mockery::mock( 'overload:SG_CachePress_Supercacher' );
		$sg_cachepress_supercacher->shouldReceive( 'purge_cache' )->once();

		( new SiteGround() )->clean_supercacher();

		unset( $sg_cachepress_environment, $sg_cachepress_supercacher );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNoOpWhenSupercacherNotActive() {
		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '5.5' ] );
		Functions\when( 'get_option' )->justReturn( 0 );

		$purge_cache_spy = Mockery::mock( 'overload:SiteGround_Optimizer\Supercacher\Supercacher' );
		$purge_cache_spy->shouldReceive( 'purge_cache' )->never();

		( new SiteGround() )->clean_supercacher();
	}
}
