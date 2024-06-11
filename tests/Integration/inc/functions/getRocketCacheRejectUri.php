<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering ::get_rocket_cache_reject_uri
 *
 * @group Functions
 * @group Options
 */
class Test_GetRocketCacheRejectUri extends TestCase {
	private $cache_reject_uri;

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'update_option_wp_rocket_settings', '' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'update_option_wp_rocket_settings' );

		remove_filter( 'rocket_cache_reject_uri', [ $this, 'filter_rocket_cache_reject_uri' ] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->mergeExistingSettingsAndUpdate( $config['options'] );

		Functions\when( 'rocket_get_home_dirname' )->justReturn( $config['home_dirname'] );

		$this->cache_reject_uri = $config['filter_rocket_cache_reject_uri'];

		add_filter( 'rocket_cache_reject_uri', [ $this, 'filter_rocket_cache_reject_uri' ] );

		$this->assertSame(
			$expected,
			get_rocket_cache_reject_uri( true )
		);
	}

	public function filter_rocket_cache_reject_uri() {
		return $this->cache_reject_uri;
	}
}
