<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Drivers\DriverInterface;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::rewrite
 * @group  CDN
 */
class Test_Rewrite extends TestCase {
	private $cdn;
	private $options;

	public function setUp(): void {
		parent::setUp();

		$this->cdn     = Mockery::mock( CDN::class );
		$this->options = Mockery::mock( Options_Data::class );

		Functions\when( 'is_rocket_post_excluded_option' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'https://example.org' );
		Functions\when( 'add_query_arg' )->justReturn( '' );
	}

	public function addDataProvider(): array {
		return $this->getTestData( __DIR__, 'rewriteUnit' );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldRewriteBasedOnDriver( array $config, array $expected ) {
		$driver = Mockery::mock( DriverInterface::class );
		$driver->shouldReceive( 'should_rewrite_url' )->andReturn( $config['driver_returns'] );
		$subscription_controller = Mockery::mock( SubscriptionController::class );

		$subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			$subscription_controller,
			Mockery::mock( Cache::class ),
			$this->createMock( RocketCDN::class ),
			$driver
		);

		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( $config['cdn_enabled'] );

		$subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( $config['subscription_eligible'] );

		if ( $config['subscription_eligible'] && $config['driver_returns'] ) {
			$this->cdn->shouldReceive( 'rewrite' )
				->once()
				->andReturn( $config['rewritten_html'] );
		} else {
			$this->cdn->shouldNotReceive( 'rewrite' );
		}

		$this->assertSame( $expected['html'], $subscriber->rewrite( $config['html'] ) );
	}
}
