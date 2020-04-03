<?php

namespace WP_Rocket\Tests\Integration\Inc\Common;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::do_admin_post_rocket_purge_cache
 * @group admin
 * @group Common
 * @group AdminOnly
 */
class Test_DoAdminPostRocketPurgeCache extends TestCase {

	public function setUp() {
		parent::setUp();

		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_purge_cache' );
		$user = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user );

		Functions\when( 'wp_safe_redirect' )->justReturn( null );
		Functions\when( 'wp_die' )->justReturn( null );
	}

	public function testShouldTriggerHook() {
		// Post.
		$_GET['type']     = 'post-123';
		$_GET['_wpnonce'] = wp_create_nonce( 'purge_cache_post-123' );

		do_admin_post_rocket_purge_cache();

		$this->assertSame( 1, did_action( 'rocket_purge_cache' ) );

		// All.
		$_GET['type']     = 'all';
		$_GET['_wpnonce'] = wp_create_nonce( 'purge_cache_all' );

		do_admin_post_rocket_purge_cache();

		$this->assertSame( 2, did_action( 'rocket_purge_cache' ) );
	}

	public function testShouldNotTriggerHook() {
		Functions\expect( 'wp_nonce_ays' )->once();

		// Invalid type.
		$_GET['type']     = 'invalid';
		$_GET['_wpnonce'] = wp_create_nonce( 'purge_cache_invalid' );

		do_admin_post_rocket_purge_cache();

		$this->assertSame( 0, did_action( 'rocket_purge_cache' ) );
	}
}
