<?php
namespace WP_Rocket\Tests\Unit\Subscriber\Cache\PurgeActionsSubscriber;

use WP_Rocket\Subscriber\Cache\PurgeActionsSubscriber;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Subscriber\Cache\PurgeActionsSubscriber:purge_user_cache
 * @group purge_actions
 */
class Test_PurgeUserCache extends TestCase {
	/**
	 * Test should not purge user cache when user cache is disabled
	 */
	public function testShouldNotPurgeUserCacheWhenUserCacheDisabled() {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[
				'cache_logged_user',
				0,
				0,
			],
		];

		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		Functions\expect( 'rocket_clean_user' )->never();

		( new PurgeActionsSubscriber( $options ) )->purge_user_cache( 1 );
	}

	/**
	 * Test should not purge user cache when commonuser cache is enabled
	 */
	public function testShoulNotPurgeUserCacheWhenCommonUserCacheEnabled() {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[
				'cache_logged_user',
				0,
				1,
			],
		];

		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		Filters\expectApplied( 'rocket_common_cache_logged_users' )
		->once()
		->andReturn( true );

		Functions\expect( 'rocket_clean_user' )->never();

		( new PurgeActionsSubscriber( $options ) )->purge_user_cache( 1 );
	}

	/**
	 * Test should purge cache for user ID = 1
	 */
	public function testShouldPurgeCacheForUserID1() {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[
				'cache_logged_user',
				0,
				1,
			],
		];

		$options->method( 'get' )->will( $this->returnValueMap( $map ) );

		Functions\expect( 'rocket_clean_user' )
		->once()
		->with(1);

		( new PurgeActionsSubscriber( $options ) )->purge_user_cache( 1 );
	}
}
