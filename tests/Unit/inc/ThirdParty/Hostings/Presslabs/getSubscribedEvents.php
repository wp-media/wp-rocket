<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Presslabs;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Presslabs;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Presslabs::get_subscribed_events
 *
 * @group Presslabs
 * @group ThirdParty
 * @group Hostings
 */
class Test_GetSubscribedEvents extends TestCase {
	public function testShouldReturnBaseEventsWithoutCdnHost() {
		$events = Presslabs::get_subscribed_events();

		$this->assertSame( [ 'clean_files', 0 ], $events['pl_pre_cache_refresh'] );
		$this->assertSame( 'return_false', $events['rocket_display_varnish_options_tab'] );
		$this->assertSame( [ 'return_false', PHP_INT_MAX ], $events['do_rocket_generate_caching_files'] );
		$this->assertSame( [ 'return_empty_array', PHP_INT_MAX ], $events['rocket_cache_mandatory_cookies'] );
		$this->assertSame( [ 'clean_home', 10, 2 ], $events['after_rocket_clean_home'] );
		$this->assertSame( [ 'clean_post', 2 ], $events['after_rocket_clean_file'] );
		$this->assertSame( 'clean_files', $events['pl_pre_url_button_cache_refresh'] );
		$this->assertSame( 'remove_partial_purge_hooks', $events['wp_rocket_loaded'] );
		$this->assertArrayNotHasKey( 'rocket_cdn_cnames', $events );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldAddCdnCnamesEventWhenPlCdnHostDefinedAndOffloadNotDisabled() {
		if ( ! defined( 'PL_CDN_HOST' ) ) {
			define( 'PL_CDN_HOST', 'cdn.example.com' );
		}

		$events = Presslabs::get_subscribed_events();

		$this->assertSame( [ 'add_pl_cdn', 1 ], $events['rocket_cdn_cnames'] );
	}

	/**
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 */
	public function testShouldNotAddCdnCnamesEventWhenOffloadDisabled() {
		if ( ! defined( 'DISABLE_CDN_OFFLOAD' ) ) {
			define( 'DISABLE_CDN_OFFLOAD', true );
		}
		if ( ! defined( 'PL_CDN_HOST' ) ) {
			define( 'PL_CDN_HOST', 'cdn.example.com' );
		}

		$events = Presslabs::get_subscribed_events();

		$this->assertArrayNotHasKey( 'rocket_cdn_cnames', $events );
	}
}
