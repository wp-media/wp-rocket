<?php

namespace WP_Rocket\Tests\Unit\Inc\Common;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers ::do_admin_post_rocket_purge_cache
 * @group Common
 */
class Test_DoAdminPostRocketPurgeCache extends TestCase {

	protected function setUp() {
		parent::setUp();

		Functions\when( 'get_option' )->alias(
			function( $option ) {
				return 'stylesheet' === $option ? 'twenty-foobar' : false;
			}
		);

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

		Functions\when( 'wp_verify_nonce' )->justReturn( true );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'set_transient' )->justReturn( null );
		Functions\when( 'wp_get_referer' )->justReturn( 'http://example.org' );
		Functions\when( 'esc_url_raw' )->returnArg();
		Functions\when( 'wp_safe_redirect' )->justReturn( null );
		Functions\expect( 'wp_die' )->once();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/common/purge.php';
	}

	public function testShouldTriggerHook() {
		$_GET['type']     = 'post-123';
		$_GET['_wpnonce'] = 'whatever';

		Functions\when( 'rocket_clean_post' )->justReturn( null );
		Actions\expectDone( 'rocket_purge_cache' )
			->once()
			->with( 'post', 123, '', '' );

		do_admin_post_rocket_purge_cache();
	}
}
