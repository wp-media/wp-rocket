<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Cache\PurgeActionsSubscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Integration\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Subscriber\Cache\PurgeActionsSubscriber:purge_user_cache
 * @group purge_actions
 */
class Test_PurgeUserCache extends FilesystemTestCase {
	private static $user_id;

	public static function wpSetUpBeforeClass( $factory ) {
		self::$user_id = $factory->user->create( [ 'user_login' => 'wpmedia', 'role' => 'editor' ] );
	}

	public function setUp() {
		parent::setUp();

		// Unhook WooCommerce, as it throws wpdb::prepare errors.
		remove_action( 'delete_user', 'wc_delete_user_data' );
	}

	public function tearDown() {
		parent::tearDown();

		// Rewire WooCommerce.
		add_action( 'delete_user', 'wc_delete_user_data' );
	}

	public function testShouldNotPurgeUserCacheWhenUserCacheDisabled() {
		add_filter( 'pre_get_rocket_option_cache_logged_user', '__return_false' );

		Functions\expect( 'rocket_clean_user' )->never();

		do_action( 'delete_user', self::$user_id );

		// TODO Integrate with vfsStream.

		remove_filter( 'pre_get_rocket_option_cache_logged_user', '__return_false' );
	}

	public function testShoulNotPurgeUserCacheWhenCommonUserCacheEnabled() {
		add_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );
		add_filter( 'rocket_common_cache_logged_users', '__return_true' );

		Functions\expect( 'rocket_clean_user' )->never();

		do_action( 'delete_user', self::$user_id );

		// TODO Integrate with vfsStream.

		remove_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );
		remove_filter( 'rocket_common_cache_logged_users', '__return_true' );
	}

	public function testShouldPurgeCacheForUser() {
		add_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );

		Functions\expect( 'rocket_clean_user' )
			->once()
			->with( self::$user_id );

		do_action( 'delete_user', self::$user_id );

		// TODO Integrate with vfsStream.

		remove_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );
	}
}
