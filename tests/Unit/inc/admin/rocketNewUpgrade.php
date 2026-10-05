<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_new_upgrade
 * @group admin
 * @group upgrade
 */
class Test_RocketNewUpgrade extends TestCase {
	public function setUp() : void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/upgrader.php';
	}

	public function testShouldRegenerateAdvancedCacheFile() {
		Functions\when( 'rocket_is_ssl_website' )->justReturn( false );
		Functions\expect( 'rocket_generate_advanced_cache_file' )
			->once();
		Functions\expect( 'rocket_clean_cache_busting' )
			->once();
		// Once for the 3.6 block, once for the path bound cleanup.
		Functions\expect( 'rocket_clean_domain' )
			->twice();
		Functions\expect( 'rocket_generate_config_file' )
			->once();
		Functions\expect( 'rocket_clean_minify' )
			->with( 'css' )
			->once();
		Functions\expect( 'rocket_get_constant' )
			->with( 'WP_ROCKET_SLUG' )
			->andReturn( 'wp_rocket_settings' );
		Functions\expect( 'get_option' )
			->times( 2 )
			->andReturn( [] );
		Functions\expect( 'update_option' )
			->once();
		Functions\when( 'wp_clear_scheduled_hook' )->justReturn( 1 );
		Functions\when( 'rocket_rrmdir' )->justReturn( 1 );
		Functions\expect( 'delete_transient' )
			->once()->with( 'wp_rocket_pricing' );
		Functions\expect( 'flush_rewrite_rules' )
			->once();
		rocket_new_upgrade( '3.7', '3.4.4' );
	}

	/**
	 * Upgrading from below the path bound clears the cache.
	 *
	 * @return void
	 */
	public function testShouldClearTheCacheWhenUpdatingFromBeforeThePathBound() {
		Functions\when( 'rocket_is_ssl_website' )->justReturn( false );
		Functions\when( 'rocket_generate_advanced_cache_file' )->justReturn( null );
		Functions\when( 'rocket_clean_cache_busting' )->justReturn( null );
		Functions\when( 'rocket_generate_config_file' )->justReturn( null );
		Functions\when( 'rocket_clean_minify' )->justReturn( null );
		Functions\when( 'rocket_get_constant' )->justReturn( 'wp_rocket_settings' );
		Functions\when( 'wp_clear_scheduled_hook' )->justReturn( 1 );
		Functions\when( 'rocket_rrmdir' )->justReturn( 1 );
		Functions\when( 'delete_transient' )->justReturn( true );
		Functions\when( 'flush_rewrite_rules' )->justReturn( null );

		Functions\when( 'get_option' )->justReturn( [] );
		Functions\when( 'update_option' )->justReturn( true );

		// Files written under the pre-bound names are addressed by the .htaccess rules and are no
		// longer produced by purge, so they have to go.
		Functions\expect( 'rocket_clean_domain' )
			->once();

		rocket_new_upgrade( '3.24', '3.23.3.3' );
	}

	/**
	 * Upgrading from a version that already has the bound does not clear the cache.
	 *
	 * @return void
	 */
	public function testShouldNotClearTheCacheWhenAlreadyPastThePathBound() {
		Functions\when( 'rocket_is_ssl_website' )->justReturn( false );
		Functions\when( 'rocket_generate_advanced_cache_file' )->justReturn( null );
		Functions\when( 'rocket_clean_cache_busting' )->justReturn( null );
		Functions\when( 'rocket_generate_config_file' )->justReturn( null );
		Functions\when( 'rocket_clean_minify' )->justReturn( null );
		Functions\when( 'rocket_get_constant' )->justReturn( 'wp_rocket_settings' );
		Functions\when( 'update_option' )->justReturn( true );
		Functions\when( 'wp_clear_scheduled_hook' )->justReturn( 1 );
		Functions\when( 'rocket_rrmdir' )->justReturn( 1 );
		Functions\when( 'delete_transient' )->justReturn( true );
		Functions\when( 'flush_rewrite_rules' )->justReturn( null );
		Functions\when( 'get_option' )->justReturn( [] );

		Functions\expect( 'rocket_clean_domain' )
			->never();

		rocket_new_upgrade( '3.25', '3.24' );
	}

	public function testShouldFlushRewriteRulesWhenUpdatingFromBeforeMcpOauth() {
		Functions\when( 'rocket_is_ssl_website' )->justReturn( false );
		Functions\when( 'rocket_generate_advanced_cache_file' )->justReturn( null );
		Functions\when( 'rocket_clean_cache_busting' )->justReturn( null );
		Functions\when( 'rocket_clean_domain' )->justReturn( null );
		Functions\when( 'rocket_generate_config_file' )->justReturn( null );
		Functions\when( 'rocket_clean_minify' )->justReturn( null );
		Functions\when( 'rocket_get_constant' )->justReturn( 'wp_rocket_settings' );
		Functions\when( 'get_option' )->justReturn( [] );
		Functions\when( 'update_option' )->justReturn( true );
		Functions\when( 'wp_clear_scheduled_hook' )->justReturn( 1 );
		Functions\when( 'rocket_rrmdir' )->justReturn( 1 );
		Functions\when( 'delete_transient' )->justReturn( true );

		Functions\expect( 'flush_rewrite_rules' )
			->once();

		rocket_new_upgrade( '3.23', '3.22.1' );
	}

	public function testShouldNotFlushRewriteRulesWhenAlreadyUpdatedPastMcpOauth() {
		Functions\when( 'rocket_is_ssl_website' )->justReturn( false );
		Functions\when( 'rocket_generate_advanced_cache_file' )->justReturn( null );
		Functions\when( 'rocket_clean_cache_busting' )->justReturn( null );
		Functions\when( 'rocket_clean_domain' )->justReturn( null );
		Functions\when( 'rocket_generate_config_file' )->justReturn( null );
		Functions\when( 'rocket_clean_minify' )->justReturn( null );
		Functions\when( 'rocket_get_constant' )->justReturn( 'wp_rocket_settings' );
		Functions\when( 'get_option' )->justReturn( [] );
		Functions\when( 'update_option' )->justReturn( true );
		Functions\when( 'wp_clear_scheduled_hook' )->justReturn( 1 );
		Functions\when( 'rocket_rrmdir' )->justReturn( 1 );
		Functions\when( 'delete_transient' )->justReturn( true );

		Functions\expect( 'flush_rewrite_rules' )
			->never();

		rocket_new_upgrade( '3.24', '3.23' );
	}
}
