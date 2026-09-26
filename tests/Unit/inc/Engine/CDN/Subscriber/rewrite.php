<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\CdnStateBridge;
use WP_Rocket\Engine\CDN\Drivers\DriverFactory;
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
		$driver_factory = Mockery::mock( DriverFactory::class );
		$driver_factory->shouldReceive( 'create' )->andReturn( $driver );
		$subscription_controller = Mockery::mock( SubscriptionController::class );

		$subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			Mockery::mock( Options::class ),
			$subscription_controller,
			Mockery::mock( Cache::class ),
			$this->createMock( RocketCDN::class ),
			Mockery::mock( CdnStateBridge::class ),
			$driver_factory
		);

		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( $config['cdn_enabled'] );

		if ( $expected['rewrite_called'] ) {
			$this->cdn->shouldReceive( 'rewrite' )
				->once()
				->andReturn( $config['rewritten_html'] );
		} else {
			$this->cdn->shouldNotReceive( 'rewrite' );
		}

		$this->assertSame( $expected['html'], $subscriber->rewrite( $config['html'] ) );
	}

	public function testShouldReturnOriginalHtmlAndNeverRewriteWhenNoDriver() {
		$subscription_controller = Mockery::mock( SubscriptionController::class );

		$subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			Mockery::mock( Options::class ),
			$subscription_controller,
			Mockery::mock( Cache::class ),
			$this->createMock( RocketCDN::class ),
			Mockery::mock( CdnStateBridge::class ),
			null
		);

		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( 1 );

		$this->cdn->shouldNotReceive( 'rewrite' );

		$html = '<img src="https://example.org/wp-content/uploads/image.jpg">';

		$this->assertSame( $html, $subscriber->rewrite( $html ) );
	}

	public function testShouldResolveDriverLazilyAndMemoizeAcrossMultipleCalls() {
		$driver = Mockery::mock( DriverInterface::class );
		$driver->shouldReceive( 'should_rewrite_url' )->andReturn( true );

		$driver_factory = Mockery::mock( DriverFactory::class );
		// The factory must never be asked to resolve a driver until the first rewrite
		// actually needs one — and only once, even across multiple subsequent calls.
		$driver_factory->shouldReceive( 'create' )
			->once()
			->andReturn( $driver );

		$subscription_controller = Mockery::mock( SubscriptionController::class );

		$subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			Mockery::mock( Options::class ),
			$subscription_controller,
			Mockery::mock( Cache::class ),
			$this->createMock( RocketCDN::class ),
			Mockery::mock( CdnStateBridge::class ),
			$driver_factory
		);

		// Constructing the Subscriber must not have called create() yet — verified by the
		// ->once() expectation above only being satisfied after the calls below.

		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( 1 );

		$this->cdn->shouldReceive( 'rewrite' )
			->twice()
			->andReturnUsing(
				function ( $html ) {
					return $html;
				}
			);

		$html = '<img src="https://example.org/wp-content/uploads/image.jpg">';

		$subscriber->rewrite( $html );
		$subscriber->rewrite( $html );
	}
}
