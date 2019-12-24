<?php
namespace WP_Rocket\Tests\Integration\Subscriber\NonceSubscriber;
use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;
/**
 * @group Subscriber_TestNonce
 */
class TestNonce extends TestCase {
	protected static $user_id;
	protected static $wp_hasher;

	public static function wpSetUpBeforeClass( $factory ) {
		require_once ABSPATH . WPINC . '/class-phpass.php';
		self::$wp_hasher = new \PasswordHash( 8, true );
		// WooCommerce Generate a unique customer ID when WP Rocket generates the page because sessions are different.
		self::$user_id = md5( self::$wp_hasher->get_random_bytes( 32 ) ); // WooCommerce Generate a unique customer ID for guests.
	}

	function setUp() {
		parent::setUp();
		add_filter( 'nonce_user_logged_out', [ $this, 'woo_nonce_user_logged_out' ], 10, 2 );
	}

	/**
	 * WooCommerce changes $uid for non-logged customers based on session.
	 * When a user is logged out, ensure they have a unique nonce by using the customer/session ID.
	 *
	 * @since  3.5.1
	 * @access public
	 * @author Soponar Cristina
	 *
	 * @param int    $uid    ID of the nonce-owning user.
	 * @param string $action The nonce action.
	 *
	 * @return int $uid      ID of the nonce-owning user.
	 */
	public function woo_nonce_user_logged_out( $uid, $action ) {
		return self::$user_id ? self::$user_id : $uid;
	}

	public function testShouldNotValidateNonce() {
		//Functions\expect( 'class_exists' )->once()->with( 'WooCommerce' )->andReturn( true );

		$action                  = 'action';
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( $action );
		self::$user_id           = md5( self::$wp_hasher->get_random_bytes( 32 ) ); // WooCommerce Generate a unique customer ID when the nonce is validated because sessions are different.
		$count                   = did_action( 'wp_verify_nonce_failed' );
		$result                  = check_ajax_referer( $action, false, false );

		$this->assertEquals( $count + 1 , did_action( 'wp_verify_nonce_failed' ) );
	}

	public function testShouldValidateNonce() {
		//Functions\expect( 'class_exists' )->once()->with( 'WooCommerce' )->andReturn( true );

		$action                  = 'wcmd-subscribe-secret';
		$_REQUEST['_ajax_nonce'] = wp_create_nonce( $action );
		self::$user_id           = md5( self::$wp_hasher->get_random_bytes( 32 ) ); // WooCommerce Generate a unique customer ID when the nonce is validated because sessions are different.
		$count                   = did_action( 'wp_verify_nonce_failed' );
		$result                  = check_ajax_referer( $action, false, false );

		$this->assertEquals( $count, did_action( 'wp_verify_nonce_failed' ) );
	}
}