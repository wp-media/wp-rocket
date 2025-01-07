<?php

namespace WP_Rocket\Tests\Unit\Inc\Common;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering ::do_admin_post_rocket_purge_cache
 *
 * @group Common
 * @group vfs
 */
class Test_DoAdminPostRocketPurgeCache extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/common/doAdminPostRocketPurgeCache.php';

	public function setUp(): void {
		parent::setUp();

		Functions\expect( 'get_option' )
			->with( 'stylesheet' )
			->andReturn( 'twentytwelve' );

		// Load the file once.
		if ( ! function_exists( 'do_admin_post_rocket_purge_cache' ) ) {
			require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
		}

		Functions\when( 'sanitize_key' )->alias(
			function ( $key ) {
				$key = strtolower( $key );

				return preg_replace( '/[^a-z0-9_\-]/', '', $key );
			}
		);

		Functions\when( 'absint' )->alias(
			function ( $maybeint ) {
				return abs( intval( $maybeint ) );
			}
		);

		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return stripslashes( $value );
			}
		);

		Functions\when( 'sanitize_title' )->alias(
			function ( $value ) {
				return strtolower( $value );
			}
		);
	}

	/**
	 * @dataProvider purgeTestData
	 */
	public function testShouldPurge( $_get, array $config ) {
		foreach ( $_get as $key => $value ) {
			$_GET[ $key ] = $value;
		}

		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_purge_cache' )
			->andReturn( true );

		switch ( $config['type'] ) {
			case 'all':
				Functions\expect( 'set_transient' )->once()->with( 'rocket_clear_cache', 'all', HOUR_IN_SECONDS )->andReturnNull();
				Functions\expect( 'rocket_clean_domain' )->once()->with( $config['lang'] )->andReturnNull();
				Functions\expect( 'rocket_dismiss_box' )->once()->with( 'rocket_warning_plugin_modification' )->andReturnNull();
				Functions\expect( 'rocket_renew_box' )->once()->with( 'preload_notice' )->andReturnNull();
				break;
			case 'post':
				Functions\expect( 'set_transient' )->once()->with( 'rocket_clear_cache', 'post', HOUR_IN_SECONDS )->andReturnNull();
				Functions\expect( 'rocket_clean_post' )->once()->with( $config['post_id'] )->andReturnNull();
				break;
		}

		Actions\expectDone( 'rocket_purge_cache' )
			->once()
			->withAnyArgs();

		Functions\expect( 'wp_get_referer' )->once()->andReturn( 'http://example.org' );
		Functions\expect( 'esc_url_raw' )->once()->with( 'http://example.org' )->andReturnFirstArg();
		Functions\expect( 'wp_safe_redirect' )->once();
		Functions\expect( 'wp_die' )->once();

		do_admin_post_rocket_purge_cache();
	}

	/**
	 * @dataProvider wontPurgeTestData
	 */
	public function testShouldWontPurge( $_get, array $config ) {
		foreach ( $_get as $key => $value ) {
			$_GET[ $key ] = $value;
		}

		if ( empty( $_get['_wpnonce'] ) ) {
			Functions\expect( 'wp_verify_nonce' )->once()->andReturn( false );
			Functions\expect( 'current_user_can' )->never();
		} else {
			Functions\when( 'wp_verify_nonce' )->justReturn( true );
			Functions\expect( 'current_user_can' )->once()->with( 'rocket_purge_cache' )->andReturn( $config['current_user_can'] );
		}

		Functions\expect( 'wp_nonce_ays' )->once()->with( '' )->andReturnNull();
		Actions\expectDone( 'rocket_purge_cache' )->never();
		Functions\expect( 'wp_safe_redirect' )->never();
		Functions\expect( 'wp_die' )->never();

		do_admin_post_rocket_purge_cache();
	}

	public function purgeTestData() {
		$this->loadConfig();

		return $this->config['test_data']['purge'];
	}

	public function wontPurgeTestData() {
		$this->loadConfig();

		return $this->config['test_data']['wontpurge'];
	}
}
