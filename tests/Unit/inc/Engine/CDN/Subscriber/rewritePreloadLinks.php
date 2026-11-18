<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\CdnStateBridge;
use WP_Rocket\Engine\CDN\Drivers\DriverInterface;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::rewrite_preload_links
 *
 * @group CDN
 */
class Test_RewritePreloadLinks extends TestCase {
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
		$html      = '<link rel="preload" data-rocket-preload as="image" href="http://example.org/wp-content/uploads/a.jpg">';
		$rewritten = '<link rel="preload" data-rocket-preload as="image" href="https://cdn.example.org/wp-content/uploads/a.jpg">';

		return [
			'shouldReturnOriginalHtmlWhenCdnIsDisabled' => [
				'config'   => [
					'cdn_enabled'    => 0,
					'driver_returns' => true,
					'html'           => $html,
				],
				'expected' => [
					'html'           => $html,
					'rewrite_called' => false,
				],
			],
			'shouldReturnOriginalHtmlWhenDriverReturnsFalse' => [
				'config'   => [
					'cdn_enabled'    => 1,
					'driver_returns' => false,
					'html'           => $html,
				],
				'expected' => [
					'html'           => $html,
					'rewrite_called' => false,
				],
			],
			'shouldDelegateToCdnWhenAllowed'            => [
				'config'   => [
					'cdn_enabled'    => 1,
					'driver_returns' => true,
					'html'           => $html,
					'rewritten_html' => $rewritten,
				],
				'expected' => [
					'html'           => $rewritten,
					'rewrite_called' => true,
				],
			],
		];
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldRewritePreloadLinksOnlyWhenAllowed( array $config, array $expected ) {
		$driver = Mockery::mock( DriverInterface::class );
		$driver->shouldReceive( 'should_rewrite_url' )->andReturn( $config['driver_returns'] );

		$subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			Mockery::mock( Options::class ),
			Mockery::mock( SubscriptionController::class ),
			Mockery::mock( Cache::class ),
			$this->createMock( RocketCDN::class ),
			Mockery::mock( CdnStateBridge::class ),
			$driver
		);

		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( $config['cdn_enabled'] );

		if ( $expected['rewrite_called'] ) {
			$this->cdn->shouldReceive( 'rewrite_preload_links' )
				->once()
				->with( $config['html'] )
				->andReturn( $config['rewritten_html'] );
		} else {
			$this->cdn->shouldNotReceive( 'rewrite_preload_links' );
		}

		$this->assertSame( $expected['html'], $subscriber->rewrite_preload_links( $config['html'] ) );
	}
}
