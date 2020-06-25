<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\PurgeActionsSubscriber;

use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Engine\Cache\PurgeActionsSubscriber;

/**
 * @covers \WP_Rocket\Engine\Cache\PurgeActionsSubscriber:purge_user_cache
 * @group  purge_actions
 */
class Test_PurgeUserCache extends TestCase {
	private function getOptionsMock( $cache_logger_enabled = 1 ) {
		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$map     = [
			[ 'cache_logged_user', 0, $cache_logger_enabled ],
		];
		$options->method( 'get' )->will( $this->returnValueMap( $map ) );
		return $options;
	}

	public function testShouldNotPurgeUserCacheWhenUserCacheDisabled() {
		Functions\expect( 'rocket_clean_user' )->never();

		$subscriber = new PurgeActionsSubscriber( $this->getOptionsMock( 0 ), Mockery::mock( Purge::class ) );
		$subscriber->purge_user_cache( 1 );
	}

	public function testShoulNotPurgeUserCacheWhenCommonUserCacheEnabled() {
		Filters\expectApplied( 'rocket_common_cache_logged_users' )
			->once()
			->andReturn( true );
		Functions\expect( 'rocket_clean_user' )->never();

		$subscriber = new PurgeActionsSubscriber( $this->getOptionsMock(), Mockery::mock( Purge::class ) );
		$subscriber->purge_user_cache( 1 );
	}

	public function testShouldPurgeCacheForUserID1() {
		Functions\expect( 'rocket_clean_user' )
			->once()
			->with( 1 );

		$subscriber = new PurgeActionsSubscriber( $this->getOptionsMock(), Mockery::mock( Purge::class ) );
		$subscriber->purge_user_cache( 1 );
	}
}
