<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\CdnStateBridge;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::clear_cache_on_free_activation
 *
 * @group CDN
 */
class Test_ClearCacheOnFreeActivation extends TestCase {

	private $subscriber;
	private $cache;

	public function setUp(): void {
		parent::setUp();

		$this->cache = Mockery::mock( Cache::class );

		$this->subscriber = new Subscriber(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( CDN::class ),
			Mockery::mock( Options::class ),
			Mockery::mock( SubscriptionController::class ),
			$this->cache,
			$this->createMock( RocketCDN::class ),
			Mockery::mock( CdnStateBridge::class )
		);
	}

	public function testShouldClearAllCache(): void {
		$this->cache->expects()->clear_all_cache()->once();

		$this->subscriber->clear_cache_on_free_activation();
	}
}
