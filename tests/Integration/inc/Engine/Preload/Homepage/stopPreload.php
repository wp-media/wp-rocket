<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Homepage;

use WP_Rocket\Engine\Preload\Homepage;
use WP_Rocket\Tests\Integration\FilterTrait;
use WP_Rocket\Tests\Integration\inc\Engine\Preload\PreloadTestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Homepage::stop_homepage_preload
 * @uses   ::get_rocket_cache_query_string
 * @group  Preload
 */
class Test_StopPreload extends PreloadTestCase {
	use FilterTrait;
	protected $setUpFilters = true;
	protected $tearDownFilters = true;
	public function setUp() : void {
		parent::setUp();
		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'stop_homepage_preload', 9 );
		$this->unregisterAllCallbacksExcept( 'admin_post_rocket_rollback', 'stop_homepage_preload', 9 );
	}
	public function tearDown() {
		parent::tearDown();
		$this->restoreWpFilter( 'wp_rocket_upgrade' );
		$this->restoreWpFilter( 'admin_post_rocket_rollback' );
	}
	public function testShouldStopPreloadRollback() {
		$this->startPreload();
		do_action('admin_post_rocket_rollback');
		$is_cron_exists= wp_get_schedule('rocket_preload_cron');

		$this->assertFalse( $is_cron_exists );
	}

	public function testShouldStopPreloadUpgrade() {
		$this->startPreload();
		do_action('wp_rocket_upgrade','3.9.4.1', '3.10');
		$is_cron_exists= wp_get_schedule('rocket_preload_cron');

		$this->assertFalse( $is_cron_exists );
	}
	private function startPreload(){
		$home_urls = [
			[ 'url' => "{$this->site_url}/mobile-preload-homepage/" ],
			[ 'url' => "{$this->site_url}/2020/02/18/mobile-preload-post-tester/" ],
			[ 'url' => "{$this->site_url}/category/mobile-preload/", 'mobile' => 1 ],
		];

		( new Homepage( $this->process ) )->preload( $home_urls );
	}
}
