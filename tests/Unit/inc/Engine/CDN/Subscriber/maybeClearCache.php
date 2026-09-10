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
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::maybe_clear_cache
 *
 * @group CDN
 */
class Test_MaybeClearCache extends TestCase {

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

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldClearAsExpected( array $config, array $expected ): void {
		switch ( $expected['method'] ) {
			case 'free_pages':
				$this->cache->expects()->clear_rocketcdn_free_pages_cache()->once();
				$this->cache->expects()->clear_all_cache()->never();
				break;

			case 'all':
				$this->cache->expects()->clear_all_cache()->once();
				$this->cache->expects()->clear_rocketcdn_free_pages_cache()->never();
				break;

			case 'none':
				$this->cache->expects()->clear_all_cache()->never();
				$this->cache->expects()->clear_rocketcdn_free_pages_cache()->never();
				break;
		}

		$this->subscriber->maybe_clear_cache( $config['old_value'], $config['new_value'] );
	}
}
