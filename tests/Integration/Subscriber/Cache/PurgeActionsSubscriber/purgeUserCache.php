<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Cache\PurgeActionsSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Subscriber\Cache\PurgeActionsSubscriber:purge_user_cache
 * @group purge_actions
 */
class Test_PurgeUserCache extends TestCase {
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

	/**
	 * Test should not purge user cache when user cache is disabled
	 */
	public function testShouldNotPurgeUserCacheWhenUserCacheDisabled() {
		add_filter( 'pre_get_rocket_option_cache_logged_user', '__return_false' );

		Functions\expect( 'rocket_clean_user' )
			->never();

		do_action( 'delete_user', 1 );

		remove_filter( 'pre_get_rocket_option_cache_logged_user', '__return_false' );
	}

	/**
	 * Test should not purge user cache when commonuser cache is enabled
	 */
	public function testShoulNotPurgeUserCacheWhenCommonUserCacheEnabled() {
		add_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );
		add_filter( 'rocket_common_cache_logged_users', '__return_true' );

		Functions\expect( 'rocket_clean_user' )
			->never();

		do_action( 'delete_user', 1 );

		remove_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );
		remove_filter( 'rocket_common_cache_logged_users', '__return_true' );
	}

	/**
	 * Test should purge cache for user ID = 1
	 */
	public function testShouldPurgeCacheForUserID1() {
		add_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );

		Functions\expect( 'rocket_clean_user' )
			->once()
			->with( 1 );

		do_action( 'delete_user', 1 );

		remove_filter( 'pre_get_rocket_option_cache_logged_user', '__return_true' );
	}
}
