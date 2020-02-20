<?php

namespace WP_Rocket\Tests\Integration\inc\Addons\Cloudflare\Subscriber;

use Brain\Monkey\Functions;
use WPDieException;

/**
 * @covers WPMedia\Cloudflare\Subscriber::purge_cache
 * @covers WPMedia\Cloudflare\Subscriber::purge_cache_no_die
 * @group  DoCloudflare
 * @group  Addons
 */
class Test_PurgeCache extends TestCase {

	public function setUp() {
		parent::setUp();

		unset( $_GET['_wpnonce'] );
		set_current_screen( 'settings_page_wprocket' );
	}

	public function testShouldWPNonceAysWhenNonceIsMissing() {
		Functions\expect( 'wp_verify_nonce' )->never();
		Functions\expect( 'sanitize_key' )->never();
		$this->expectException( WPDieException::class );
		$this->expectExceptionMessage( 'The link you followed has expired.' );
		do_action( 'admin_post_rocket_purge_cloudflare' );
	}

	public function testShouldWPNonceAysWhenNonceInvalid() {
		$_GET['_wpnonce'] = 'invalid';

		Functions\expect( 'current_user_can' )->never();
		Functions\expect( 'wp_safe_redirect' )->never();

		$this->expectException( WPDieException::class );
		$this->expectExceptionMessage( 'The link you followed has expired.' );
		do_action( 'admin_post_rocket_purge_cloudflare' );
	}

	public function testShouldBailoutWhenUserCantPurgeCF() {
		$this->setApiCredentialsInOptions();

		$user_id = $this->factory->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $user_id );
		$this->assertFalse( current_user_can( 'rocket_purge_cloudflare' ) );

		$this->setRedirect();
		$this->setNonce();

		// Run it.
		do_action( 'admin_post_rocket_purge_cloudflare' );

		// Just to make sure the transient did not get set.
		$this->assertFalse( get_transient( "{$user_id}_cloudflare_purge_result" ) );

		$this->cleanUp( $user_id );
	}

	public function testShouldPurgeWhenUserCanPurgeCF() {
		$this->setApiCredentialsInOptions();

		// Set the user who can purge Cloudflare.
		$admin = get_role( 'administrator' );
		$admin->add_cap( 'rocket_purge_cloudflare_cache' );
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		$this->assertTrue( current_user_can( 'rocket_purge_cloudflare_cache' ) );

		// Set the nonce and redirect.
		$this->setRedirect();
		$this->setNonce();

		// Run it.
		do_action( 'admin_post_rocket_purge_cloudflare' );

		// Check that the transient was set.
		$this->assertSame(
			[
				'result'  => 'success',
				'message' => '<strong>WP Rocket:</strong> Cloudflare cache successfully purged.',
			],
			get_transient( "{$user_id}_cloudflare_purge_result" )
		);

		$this->cleanUp( $user_id );
	}

	private function setNonce() {
		$_REQUEST['_wp_http_referer'] = addslashes( 'http://example.com/wp-admin/options-general.php?page=wprocket#page_cloudflare' );
		$_SERVER['REQUEST_URI']       = $_REQUEST['_wp_http_referer'];
		$_GET['_wpnonce']             = wp_create_nonce( 'rocket_purge_cloudflare' );

		// Just checking.
		$this->assertEquals( 1, wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'rocket_purge_cloudflare' ) );
		Functions\expect( 'wp_nonce_ays' )->never();
	}

	private function setRedirect() {
		// Let's redirect anywhere.
		add_filter( 'wp_redirect', '__return_empty_string' );

		// Yes, we do expect wp_die() when running tests.
		Functions\expect( 'wp_die' )->once()->andReturn();
	}

	private function cleanUp( $user_id = 0 ) {
		if ( 0 === $user_id ) {
			$user_id = get_current_user_id();
		}

		remove_filter( 'wp_redirect', '__return_empty_string' );
		unset( $_REQUEST['_wp_http_referer'], $_SERVER['REQUEST_URI'], $_GET['_wpnonce'] );
		delete_transient( "{$user_id}_cloudflare_purge_result" );
	}
}
