<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_after_save_options
 * @group admin
 * @group upgrade
 */
class Test_RocketAfterSaveOptions extends TestCase {
	public function setUp() {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/options.php';

		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_SLUG' )
			->andReturn( 'wp_rocket_settings' )
			->andAlsoExpectIt()
			->with( 'WP_CACHE' )
			->andReturn( true );
	}

	public function testShouldNotTriggerCallbacksWhenInvalidValues() {
		Functions\expect( 'wp_json_encode' )->never();

		rocket_after_save_options( 'foo', 'bar' );
		rocket_after_save_options( [], 'bar' );
		rocket_after_save_options( 'foo', [] );
	}

	public function testShouldTriggerCleaningsWhenOptionsChange() {
		$this->commonMocks();

		Functions\expect( 'rocket_clean_minify' )->never();
		Functions\expect( 'rocket_generate_advanced_cache_file' )->never();
		Functions\expect( 'set_rocket_wp_cache_define' )->never();
		Functions\expect( 'set_transient' )->never();

		$_POST['foo'] = 1;
		$oldvalue     = [
			'cache_mobile'        => true,
			'purge_cron_interval' => true,
			'purge_cron_unit'     => true,
			'minify_css'          => false,
			'exclude_css'         => '',
			'minify_js'           => false,
			'exclude_js'          => '',
		];
		$newvalue     = [
			'cache_mobile'        => true,
			'purge_cron_interval' => true,
			'purge_cron_unit'     => false,
			'minify_css'          => false,
			'exclude_css'         => '',
			'minify_js'           => false,
			'exclude_js'          => '',
			'foobar'              => 'barbaz', // This one will trigger cleaning and preload.
		];

		rocket_after_save_options( $oldvalue, $newvalue );
	}

	private function commonMocks() {
		Functions\when( 'wp_json_encode' )->alias(
			function ( $data, $options = 0, $depth = 512 ) {
				return json_encode( $data, $options, $depth );
			}
		);
		Functions\when( 'home_url' )->alias(
			function ( $path = '' ) {
				return 'http://example.org' . $path;
			}
		);
		Functions\expect( 'rocket_clean_domain' )->once();
		Functions\expect( 'wp_remote_get' )->once();
		Functions\expect( 'rocket_valid_key' )->once()->andReturn( true );
		Functions\expect( 'flush_rocket_htaccess' )->once();
		Functions\expect( 'rocket_generate_config_file' )->once();
	}
}
