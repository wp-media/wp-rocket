<?php
declare(strict_types=1);

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
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::remove_cdn_from_tracked_options
 *
 * @covers \WP_Rocket\Engine\CDN\Subscriber::remove_cdn_from_tracked_options
 * @group  CDN
 */
class Test_RemoveCdnFromTrackedOptions extends TestCase {

	private $subscriber;

	public function set_up(): void {
		parent::set_up();

		$this->subscriber = new Subscriber(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( CDN::class ),
			Mockery::mock( Options::class ),
			Mockery::mock( SubscriptionController::class ),
			Mockery::mock( Cache::class ),
			$this->createMock( RocketCDN::class ),
			Mockery::mock( CdnStateBridge::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRemoveCdnKeyOnly( array $config, array $expected ): void {
		$this->assertSame( $expected, $this->subscriber->remove_cdn_from_tracked_options( $config['options'] ) );
	}
}
