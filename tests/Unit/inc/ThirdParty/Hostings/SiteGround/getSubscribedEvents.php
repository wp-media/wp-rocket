<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\SiteGround;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SiteGround;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SiteGround::get_subscribed_events
 *
 * The map is returned only when the supercacher is active (SG Optimizer < 5.0 via the legacy
 * $sg_cachepress_environment global, 5.0+ via the siteground_optimizer_enable_cache option), and the
 * version determines which AJAX purge hook and whether the pre-4.0.5 "force caching files" filter is
 * added. See issue #8768.
 *
 * @group SiteGround
 * @group ThirdParty
 * @group Hostings
 */
class Test_GetSubscribedEvents extends TestCase {
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
	public function testShouldReturnEmptyArrayWhenSupercacherNotActive() {
		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '5.5' ] );
		Functions\when( 'get_option' )->justReturn( 0 );

		$this->assertSame( [], SiteGround::get_subscribed_events() );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldReturnFullMapWithModernAjaxHookWhenSupercacherActive() {
		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '5.5' ] );
		Functions\when( 'get_option' )->justReturn( 1 );

		$events = SiteGround::get_subscribed_events();

		$this->assertSame( [ 'sg_clear_cache', 0 ], $events['admin_post_sg-cachepress-purge'] );
		$this->assertSame( 'clean_supercacher', $events['rocket_after_clean_domain'] );
		$this->assertSame( 'return_false', $events['rocket_display_varnish_options_tab'] );
		$this->assertSame( [ 'return_empty_array', PHP_INT_MAX ], $events['rocket_cache_mandatory_cookies'] );
		$this->assertArrayNotHasKey( 'do_rocket_generate_caching_files', $events );
		$this->assertArrayNotHasKey( 'wp_ajax_sg-cachepress-purge', $events );
		$this->assertSame( [ 'sg_clear_cache', 0 ], $events['wp_ajax_admin_bar_purge_cache'] );
	}

	/**
	 * On a version predating SG Optimizer 5.0 (and 4.0.5), the legacy AJAX hook and the "force caching
	 * files" filter apply; the supercacher is active via the legacy $sg_cachepress_environment global.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldReturnLegacyAjaxHookAndForceCachingFilesForOldVersionWhenActive() {
		global $sg_cachepress_environment;

		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '4.0.0' ] );
		Functions\when( 'get_option' )->justReturn( 0 );

		$sg_cachepress_environment = Mockery::mock( 'overload:SG_CachePress_Environment' );
		$sg_cachepress_environment->shouldReceive( 'cache_is_enabled' )->andReturn( true );

		$events = SiteGround::get_subscribed_events();

		$this->assertSame( [ 'return_true', 11 ], $events['do_rocket_generate_caching_files'] );
		$this->assertSame( [ 'sg_clear_cache', 0 ], $events['wp_ajax_sg-cachepress-purge'] );
		$this->assertArrayNotHasKey( 'wp_ajax_admin_bar_purge_cache', $events );

		unset( $sg_cachepress_environment );
	}
}
