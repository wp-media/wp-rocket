<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\SiteGround;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\SiteGround;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\SiteGround::get_subscribed_events
 *
 * The inner "is supercacher active" gate is governed by a pre-existing operator-precedence bug
 * (`! version_compare(...) < 0` is always false, so the version-based branch is unreachable and the
 * method always falls back to the `siteground_optimizer_enable_cache` option). This is preserved
 * verbatim per issue #8768 and pinned by these tests rather than "fixed".
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
	 * Pinned regression: even on a version that predates SG Optimizer 5.0 (where the legacy AJAX hook
	 * and the "force caching files" filter would apply), the option-based fallback still governs whether
	 * the subscriber is active at all, because the inner precedence bug never lets the version-based
	 * branch run.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldReturnLegacyAjaxHookAndForceCachingFilesForOldVersionWhenActive() {
		Functions\when( 'get_file_data' )->justReturn( [ 'Version' => '4.0.0' ] );
		Functions\when( 'get_option' )->justReturn( 1 );

		$events = SiteGround::get_subscribed_events();

		$this->assertSame( [ 'return_true', 11 ], $events['do_rocket_generate_caching_files'] );
		$this->assertSame( [ 'sg_clear_cache', 0 ], $events['wp_ajax_sg-cachepress-purge'] );
		$this->assertArrayNotHasKey( 'wp_ajax_admin_bar_purge_cache', $events );
	}
}
