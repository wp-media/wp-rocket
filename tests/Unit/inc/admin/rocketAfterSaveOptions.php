<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::rocket_after_save_options
 * @group admin
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

		$oldvalue = [
			'cache_mobile'        => true,
			'purge_cron_interval' => true,
			'purge_cron_unit'     => true,
			'minify_css'          => false,
			'exclude_css'         => '',
			'minify_js'           => false,
			'exclude_js'          => '',
		];
		$newvalue = [
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

	public function testShouldCleanMinifyCSSWhenOptionChanges() {
		$this->commonMocks( 2 );

		Functions\expect( 'rocket_clean_minify' )
			->times( 2 )
			->with( 'css' )
			->andAlsoExpectIt()
			->with( 'js' )
			->never();
		Functions\expect( 'rocket_generate_advanced_cache_file' )->never();
		Functions\expect( 'set_rocket_wp_cache_define' )->never();
		Functions\expect( 'set_transient' )->never();

		$_POST['foo'] = 1;
		$oldvalue     = [
			'minify_css'  => false,
			'exclude_css' => '',
			'minify_js'   => false,
			'exclude_js'  => '',
		];
		$newvalue     = [
			'minify_css'  => true,
			'exclude_css' => '',
			'minify_js'   => false,
			'exclude_js'  => '',
		];

		rocket_after_save_options( $oldvalue, $newvalue );

		$newvalue = [
			'minify_css'  => false,
			'exclude_css' => 'foobar',
			'minify_js'   => false,
			'exclude_js'  => '',
		];

		rocket_after_save_options( $oldvalue, $newvalue );

		unset( $_POST['foo'] );
	}

	public function testShouldCleanMinifyJSWhenOptionChanges() {
		$this->commonMocks( 2 );

		Functions\expect( 'rocket_clean_minify' )
			->never()
			->with( 'css' )
			->andAlsoExpectIt()
			->with( 'js' )
			->times( 2 );
		Functions\expect( 'rocket_generate_advanced_cache_file' )->never();
		Functions\expect( 'set_rocket_wp_cache_define' )->never();
		Functions\expect( 'set_transient' )->never();

		$_POST['foo'] = 1;
		$oldvalue     = [
			'minify_css'  => false,
			'exclude_css' => '',
			'minify_js'   => false,
			'exclude_js'  => '',
		];
		$newvalue     = [
			'minify_css'  => false,
			'exclude_css' => '',
			'minify_js'   => true,
			'exclude_js'  => '',
		];

		rocket_after_save_options( $oldvalue, $newvalue );

		$newvalue = [
			'minify_css'  => false,
			'exclude_css' => '',
			'minify_js'   => false,
			'exclude_js'  => 'foobar',
		];

		rocket_after_save_options( $oldvalue, $newvalue );

		unset( $_POST['foo'] );
	}

	public function testShouldCleanMinifyWhenCDNOptionChanges() {
		$this->commonMocks( 2 );

		Functions\expect( 'rocket_clean_minify' )
			->times( 2 )
			->with( 'css' )
			->andAlsoExpectIt()
			->with( 'js' )
			->times( 2 );
		Functions\expect( 'rocket_generate_advanced_cache_file' )->never();
		Functions\expect( 'set_rocket_wp_cache_define' )->never();
		Functions\expect( 'set_transient' )->never();

		$oldvalue = [
			'minify_css'  => false,
			'exclude_css' => '',
			'minify_js'   => false,
			'exclude_js'  => '',
		];
		$newvalue = $oldvalue;
		$newvalue['cdn'] = 0;

		rocket_after_save_options( $oldvalue, $newvalue );

		$oldvalue['cdn'] = 0;
		unset( $newvalue['cdn'] );

		rocket_after_save_options( $oldvalue, $newvalue );
	}

	public function testShouldGenerateAdvancedCacheFileWhenOptionChanges() {
		$this->commonMocks( 3 );

		Functions\expect( 'rocket_clean_minify' )->never();
		Functions\expect( 'rocket_generate_advanced_cache_file' )->times( 3 );
		Functions\expect( 'set_rocket_wp_cache_define' )->never();
		Functions\expect( 'set_transient' )->never();

		$_POST['foo'] = 1;
		$oldvalue     = [
			'minify_css'  => false,
			'exclude_css' => '',
			'minify_js'   => false,
			'exclude_js'  => '',
		];
		$newvalue     = $oldvalue;
		$newvalue['do_caching_mobile_files'] = 0;

		rocket_after_save_options( $oldvalue, $newvalue );

		$oldvalue['do_caching_mobile_files'] = 0;
		unset( $newvalue['do_caching_mobile_files'] );

		rocket_after_save_options( $oldvalue, $newvalue );

		$newvalue['do_caching_mobile_files'] = 2;

		rocket_after_save_options( $oldvalue, $newvalue );

		unset( $_POST['foo'] );
	}

	public function testShouldEnableAnalyticsWhenOptionIsEnabled() {
		$this->commonMocks( 1, 0 );

		Functions\expect( 'rocket_clean_minify' )->never();
		Functions\expect( 'rocket_generate_advanced_cache_file' )->never();
		Functions\expect( 'set_rocket_wp_cache_define' )->never();
		Functions\expect( 'set_transient' )
			->once()
			->with( 'rocket_analytics_optin', 1 );

		$oldvalue = [
			'minify_css'        => false,
			'exclude_css'       => '',
			'minify_js'         => false,
			'exclude_js'        => '',
			'analytics_enabled' => '0',
		];
		$newvalue     = $oldvalue;
		$newvalue['analytics_enabled'] = '1';

		rocket_after_save_options( $oldvalue, $newvalue );
	}

	private function commonMocks( $times = 1, $clean_domain_times = null ) {
		if ( ! isset( $clean_domain_times ) ) {
			$clean_domain_times = $times;
		}
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
		Functions\expect( 'rocket_clean_domain' )->times( $clean_domain_times );
		Functions\expect( 'wp_remote_get' )->times( $clean_domain_times );
		Functions\expect( 'rocket_valid_key' )->times( $times )->andReturn( true );
		Functions\expect( 'flush_rocket_htaccess' )->times( $times );
		Functions\expect( 'rocket_generate_config_file' )->times( $times );
	}
}
