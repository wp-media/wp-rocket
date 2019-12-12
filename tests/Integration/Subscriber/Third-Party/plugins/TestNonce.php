<?php
namespace WP_Rocket\Tests\Integration\Subscriber\ExpiredCachePurgeSubscriber;
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
		self::$wp_hasher = new PasswordHash( 8, true );
		self::$user_id = md5( self::$wp_hasher->get_random_bytes( 32 ) ); // WooCommerce Generate a unique customer ID for guests.
	}

	function setUp() {
		parent::setUp();
	}
	public function woo_nonce_user_logged_out( $uid, $action ) {
		return $this->user_id ? $this->user_id : $uid;
	}
}