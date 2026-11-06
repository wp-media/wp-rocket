<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Cache\PurgeActionsSubscriber;

use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Cache\Purge;
use WP_Rocket\Engine\Cache\PurgeActionsSubscriber;

/**
 * Test class covering \WP_Rocket\Engine\Cache\PurgeActionsSubscriber::purge_user_cache
 * @group  purge_actions
 */
class Test_PurgeUserCache extends TestCase {
	private $options;
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscriber = new PurgeActionsSubscriber( $this->options, Mockery::mock( Purge::class ) );
	}

	public function testShouldNotPurgeUserCacheWhenUserCacheDisabled() {
		Functions\expect( 'rocket_clean_user' )->never();

		$this->options->shouldReceive( 'get' )
			->with( 'cache_logged_user', 0 )
			->andReturn( 0 );

		$this->subscriber->purge_user_cache( 1 );
	}

	public function testShoulNotPurgeUserCacheWhenCommonUserCacheEnabled() {
		Filters\expectApplied( 'rocket_common_cache_logged_users' )
			->once()
			->andReturn( true );
		Functions\expect( 'rocket_clean_user' )->never();

		$this->options->shouldReceive( 'get' )
			->with( 'cache_logged_user', 0 )
			->andReturn( 1 );

		$this->subscriber->purge_user_cache( 1 );
	}

	public function testShouldPurgeCacheForUserID1() {
		Functions\expect( 'rocket_clean_user' )
			->once()
			->with( 1 );

		$this->options->shouldReceive( 'get' )
			->with( 'cache_logged_user', 0 )
			->andReturn( 1 );

		$this->subscriber->purge_user_cache( 1 );
	}
}
