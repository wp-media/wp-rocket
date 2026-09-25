<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\SiteGround;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SiteGround;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SiteGround::is_supercacher_active
 *
 * SG Optimizer < 5.0 exposes its state through the legacy $sg_cachepress_environment global;
 * 5.0+ stores it in the siteground_optimizer_enable_cache option. See issue #8768.
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
	public function testShouldUseEnvironmentObjectOnLegacyVersion() {
		global $sg_cachepress_environment;

		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '4.9' ] );
		// Ensure the option path is not what makes the assertion pass.
		Functions\when( 'get_option' )->justReturn( 0 );

		$sg_cachepress_environment = Mockery::mock( 'overload:SG_CachePress_Environment' );
		$sg_cachepress_environment->shouldReceive( 'cache_is_enabled' )->andReturn( true );

		$this->assertTrue( ( new SiteGround() )->is_supercacher_active() );

		unset( $sg_cachepress_environment );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldReturnFalseOnLegacyVersionWhenEnvironmentCacheDisabled() {
		global $sg_cachepress_environment;

		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '4.9' ] );
		Functions\when( 'get_option' )->justReturn( 1 );

		$sg_cachepress_environment = Mockery::mock( 'overload:SG_CachePress_Environment' );
		$sg_cachepress_environment->shouldReceive( 'cache_is_enabled' )->andReturn( false );

		$this->assertFalse( ( new SiteGround() )->is_supercacher_active() );

		unset( $sg_cachepress_environment );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldUseOptionOnModernVersion() {
		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '5.5' ] );
		Functions\when( 'get_option' )->justReturn( 1 );

		$this->assertTrue( ( new SiteGround() )->is_supercacher_active() );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldReturnFalseOnModernVersionWhenOptionDisabled() {
		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '5.5' ] );
		Functions\when( 'get_option' )->justReturn( 0 );

		$this->assertFalse( ( new SiteGround() )->is_supercacher_active() );
	}
}
