<?php

namespace WP_Rocket\Tests\Unit\Inc\Common;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::do_admin_post_rocket_purge_cache
 * @group Common
 * @runTestsInSeparateProcesses
 */
class Test_DoAdminPostRocketPurgeCache extends TestCase {

	protected function setUp() {
		parent::setUp();

		Functions\when( 'sanitize_key' )->alias(
			function( $key ) {
				$key = strtolower( $key );
				return preg_replace( '/[^a-z0-9_\-]/', '', $key );
			}
		);

		Functions\when( 'absint' )->alias(
			function( $maybeint ) {
				return abs( intval( $maybeint ) );
			}
		);

		Functions\when( 'wp_unslash' )->alias(
			function( $value ) {
				return stripslashes( $value );
			}
		);

		Functions\when( 'sanitize_title' )->alias(
			function( $value ) {
				return strtolower( $value );
			}
		);

		Functions\when( 'get_option' )->justReturn( '' );
		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'set_transient' )->justReturn( null );
		Functions\when( 'wp_get_referer' )->justReturn( 'http://example.org' );
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'wp_safe_redirect' )->justReturn( null );
		Functions\when( 'wp_nonce_ays' )->justReturn( null );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';

		Functions\when( 'rocket_clean_post' )->justReturn( null );
		Functions\when( 'rocket_clean_domain' )->justReturn( null );
	}

	public function testShouldTriggerHook() {
		Functions\expect( 'wp_die' )->twice();

		// Post.
		$_GET['type']     = 'post-123';
		$_GET['_wpnonce'] = 'whatever';

		Actions\expectDone( 'rocket_purge_cache' )
			->once()
			->with( 'post', 123, '', '' );

		do_admin_post_rocket_purge_cache();

		// All.
		$_GET['type']     = 'all';
		$_GET['_wpnonce'] = 'whatever';
		$_GET['lang']     = 'en';

		Functions\when( 'get_rocket_option' )->justReturn( false );
		Functions\when( 'rocket_dismiss_box' )->justReturn( null );

		Actions\expectDone( 'rocket_purge_cache' )
			->once()
			->with( 'all', 0, '', '' );

		do_admin_post_rocket_purge_cache();
	}

	public function testShouldNotTriggerHook() {
		Functions\expect( 'wp_die' )->never();

		// Invalid type.
		$_GET['type']     = 'invalid';
		$_GET['_wpnonce'] = 'whatever';

		Actions\expectDone( 'rocket_purge_cache' )
			->never();

		do_admin_post_rocket_purge_cache();
	}
}
