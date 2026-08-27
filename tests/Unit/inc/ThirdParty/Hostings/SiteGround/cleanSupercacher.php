<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\SiteGround;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SiteGround;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SiteGround::clean_supercacher
 *
 * Pinned regression for the pre-existing bugs ported verbatim from legacy siteground.php:
 * the `! version_compare(...) < 0` operator-precedence bug means the
 * `SiteGround_Optimizer\Supercacher\Supercacher::purge_cache()` branch is never reachable, and the
 * `elseif` branch is also always a no-op because it never declares `global $sg_cachepress_supercacher;`
 * (so `isset()` is always false there). See issue #8768 — do not fix, only pin.
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
	public function testShouldNeverCallSupercacherPurgeCacheEvenWhenActive() {
		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '5.5' ] );
		Functions\when( 'get_option' )->justReturn( 1 );

		$purge_cache_spy = Mockery::mock( 'overload:SiteGround_Optimizer\Supercacher\Supercacher' );
		$purge_cache_spy->shouldReceive( 'purge_cache' )->never();

		( new SiteGround() )->clean_supercacher();
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
