<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Subscriber\Plugin\Capabilities_Subscriber;
use WPDieException;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::purge_cdn_cache
 *
 * @uses ::rocket_get_constant
 *
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_PurgeCdnCache extends TestCase {

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		( new Capabilities_Subscriber() )->add_rocket_capabilities();
	}

	public function setUp() {
		parent::setUp();

		unset( $_GET['_wpnonce'] );
		set_current_screen( 'settings_page_wprocket' );
	}

	/**
	 * Test should display "The link you followed has expired." message (via wp_nonce_asy) when the nonce is missing.
	 */
	public function testShouldWPNonceAysWhenNonceIsMissing() {
		Functions\expect( 'current_user_can' )->never();
		$this->expectException( WPDieException::class );
		$this->expectExceptionMessage( 'The link you followed has expired.' );
		do_action( 'admin_post_rocket_purge_rocketcdn' );
	}

	/**
	 * Test should display "The link you followed has expired." message (via wp_nonce_asy) when the nonce is invalid.
	 */
	public function testShouldWPNonceAysWhenNonceInvalid() {
		$_GET['_wpnonce'] = 'invalid';

		Functions\expect( 'current_user_can' )->never();

		$this->expectException( WPDieException::class );
		$this->expectExceptionMessage( 'The link you followed has expired.' );
		do_action( 'admin_post_rocket_purge_rocketcdn' );
	}

	/**
	 * Test should wp_die() when the current user doesn't have 'rocket_manage_options' capability.
	 */
	public function testShouldWPDieWhenCurrentUserCant() {
		$user_id = $this->factory->user->create( [ 'role' => 'contributor' ] );
		wp_set_current_user( $user_id );
		$_GET['_wpnonce'] = wp_create_nonce( 'rocket_purge_rocketcdn' );

		$this->assertFalse( current_user_can( 'rocket_manage_options' ) );

		Functions\expect( 'set_transient' )->never();

		$this->expectException( WPDieException::class );
		do_action( 'admin_post_rocket_purge_rocketcdn' );
	}

	/**
	 * Test should set the transient and redirect when the current user does have 'rocket_manage_options' capability.
	 *
	 * Note: Not setting the subscription ID to ensure `purge_cache_request` just returns without calling the RocketCDN
	 * API.
	 */
	public function testSetTransientAndRedirectWhenCurrentUserCan() {
		// Set up everything.
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		$_REQUEST['_wp_http_referer'] = addslashes( 'http://example.com/wp-admin/options-general.php?page=wprocket#page_cdn' );
		$_SERVER['REQUEST_URI']       = $_REQUEST['_wp_http_referer'];
		$_GET['_wpnonce']             = wp_create_nonce( 'rocket_purge_rocketcdn' );
		add_filter( 'wp_redirect', '__return_empty_string' );

		// Yes, we do expect wp_die() when running tests.
		$this->expectException( WPDieException::class );

		// Run it.
		do_action( 'admin_post_rocket_purge_rocketcdn' );

		$this->assertTrue( current_user_can( 'rocket_manage_options' ) );

		// Check that the transient was set.
		$this->assertSame(
			[
				'status'  => 'error',
				'message' => 'RocketCDN cache purge failed: Missing identifier parameter.',
			],
			get_transient( 'rocketcdn_purge_cache_response' )
		);

		// Clean up.
		remove_filter( 'wp_redirect', '__return_empty_string' );
	}
}
