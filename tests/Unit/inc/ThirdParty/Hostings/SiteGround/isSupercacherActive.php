<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\SiteGround;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SiteGround;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SiteGround::is_supercacher_active
 *
 * Pinned regression for the pre-existing operator-precedence bug ported verbatim from
 * legacy siteground.php: `! version_compare(...) < 0` is always false (bool cast to int is
 * never negative), so the `$sg_cachepress_environment`-based branch is never reachable and the
 * method always falls through to the `siteground_optimizer_enable_cache` option, regardless of
 * the SG Optimizer version or the state of `$sg_cachepress_environment`. See issue #8768.
 *
 * @group SiteGround
 * @group ThirdParty
 * @group Hostings
 */
class Test_IsSupercacherActive extends TestCase {
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
	public function testShouldIgnoreEnvironmentObjectEvenOnModernVersion() {
		global $sg_cachepress_environment;

		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '5.5' ] );
		Functions\when( 'get_option' )->justReturn( 0 );

		// If the version-compare branch were reachable, this real, matching, cache-enabled
		// environment object would make the method return true. It doesn't, because the
		// precedence bug never lets that branch execute.
		$sg_cachepress_environment = Mockery::mock( 'overload:SG_CachePress_Environment' );
		$sg_cachepress_environment->shouldReceive( 'cache_is_enabled' )->andReturn( true );

		$this->assertFalse( ( new SiteGround() )->is_supercacher_active() );

		unset( $sg_cachepress_environment );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldFallBackToOptionRegardlessOfVersion() {
		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '2.0.0' ] );
		Functions\when( 'get_option' )->justReturn( 1 );

		$this->assertTrue( ( new SiteGround() )->is_supercacher_active() );
	}
}
