<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber;

use WC_Session_Handler;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers  \WP_Rocket\ThirdParty\Plugins\Ecommerce\WooCommerceSubscriber::maybe_revert_uid_for_nonce_actions
 * @group   WithWoo
 */
class Test_MaybeRevertUidForNonceActions extends TestCase {

	public function setUp() {
		parent::setUp();

		$this->handler = new WC_Session_Handler();
	}

	public function testShouldNotValidateNonce() {
		// Create Woo Session and set cookie when WPR caches the page.
		$this->create_session();
		$action                  = 'action';
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( $action );
		$count                   = did_action( 'wp_verify_nonce_failed' );
		// Create WOO Session and set cookie when the form is submited and the nonce is validated.
		$this->create_session();
		check_ajax_referer( $action, false, false );

		$this->assertEquals( $count + 1, did_action( 'wp_verify_nonce_failed' ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldValidateNonce( $action ) {
		// Create Woo Session and set cookie when WPR caches the page.
		$this->create_session();
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( $action );
		$count                   = did_action( 'wp_verify_nonce_failed' );
		// Create WOO Session and set cookie when the form is submited and the nonce is validated.
		$this->create_session();
		check_ajax_referer( $action, false, false );

		$this->assertEquals( $count, did_action( 'wp_verify_nonce_failed' ) );
	}

	public function providerTestData() {
		return [
			[ 'wcmd-subscribe-secret' ],
			[ 'td-block' ],
			[ 'codevz_selective_refresh' ],
			[ 'xtra_quick_view' ],
			[ 'ajax_search_nonce' ],
			[ 'xtra_wishlist_content' ],
			[ 'ajax-login-security' ],
		];
	}

	public function testShouldReturnGivenUserIdWhenActionNotInList() {
		wp_set_current_user( $this->factory->user->create() );

		$nonce                       = wp_create_nonce( __METHOD__ );
		$verified_nonce_failed_count = did_action( 'wp_verify_nonce_failed' );

		// Test when a user is logged in.
		$this->assertSame( 1, wp_verify_nonce( $nonce, __METHOD__ ) );
		$this->assertEquals( $verified_nonce_failed_count, did_action( 'wp_verify_nonce_failed' ) );

		// Test when there isn't a user logged in.
		wp_set_current_user( 0 );
		$this->assertFalse( wp_verify_nonce( $nonce, __METHOD__ ) );
		$this->assertEquals( $verified_nonce_failed_count + 1, did_action( 'wp_verify_nonce_failed' ) );
	}

	/**
	 * Create WooCommerce SESSION and set COOKIE.
	 */
	protected function create_session() {
		$this->handler->init();
		$session_customer_id    = $this->handler->generate_customer_id();
		$session_expiration     = time() + intval( apply_filters( 'wc_session_expiration', 60 * 60 * 48 ) );
		$session_expiry         = time() + intval( apply_filters( 'wc_session_expiring', 60 * 60 * 47 ) );
		$to_hash                = $session_customer_id . '|' . $session_expiration;
		$cookie_hash            = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
		$cookie_value           = $session_customer_id . '||' . $session_expiration . '||' . $session_expiry . '||' . $cookie_hash;
		$woo_cookie             = apply_filters( 'woocommerce_cookie', 'wp_woocommerce_session_' . COOKIEHASH );
		$_COOKIE[ $woo_cookie ] = $cookie_value;
	}
}
