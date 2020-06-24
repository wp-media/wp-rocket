<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPEngine;

use Mockery;
use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Engine\Cache\AdminSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPEngine::remove_notices
 *
 * @group  WPEngine
 * @group  ThirdParty
 */
class Test_RemoveNotices extends WPEngineTestCase {

	public function testShouldRemoveNotices() {
		$container              = Mockery::mock( 'container' );
		$admin_cache_subscriber = Mockery::mock( AdminSubscriber::class );

		Filters\expectApplied( 'rocket_container' )
			->andReturn( $container );

		$container->shouldReceive( 'get' )
		          ->with( 'admin_cache_subscriber' )
		          ->andReturn( $admin_cache_subscriber );

		Functions\expect( 'remove_action' )
			->once()
			->with( 'admin_notices', [ $admin_cache_subscriber, 'notice_advanced_cache_permissions' ] );

		$this->wpengine->remove_notices();
	}
}
