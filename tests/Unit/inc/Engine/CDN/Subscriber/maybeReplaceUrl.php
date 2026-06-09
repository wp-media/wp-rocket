<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Drivers\DriverInterface;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::maybe_replace_url
 * @group  CDN
 */
class Test_MaybeReplaceUrl extends TestCase {
	private $cdn;
	private $options;
	private $subscriber;

	private $subscription_controller;

	private $query;

	public function setUp() : void {
		parent::setUp();

		Functions\when( 'get_rocket_parse_url' )->alias( function( $url ) {
			$parsed = parse_url( $url );

			$host     = isset( $parsed['host'] ) ? strtolower( urldecode( $parsed['host'] ) ) : '';
			$path     = isset( $parsed['path'] ) ? urldecode( $parsed['path'] ) : '';
			$scheme   = isset( $parsed['scheme'] ) ? urldecode( $parsed['scheme'] ) : '';
			$query    = isset( $parsed['query'] ) ? urldecode( $parsed['query'] ) : '';
			$fragment = isset( $parsed['fragment'] ) ? urldecode( $parsed['fragment'] ) : '';

			return [
				'host'     => $host,
				'path'     => $path,
				'scheme'   => $scheme,
				'query'    => $query,
				'fragment' => $fragment,
			];
		} );

		$this->cdn        = Mockery::mock( CDN::class );
		$this->options    = Mockery::mock( Options_Data::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->query = $this->createMock( RocketCDN::class );

		$this->subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			Mockery::mock( Options::class ),
			$this->subscription_controller,
			Mockery::mock( Cache::class ),
			$this->query
		);
	}

	public function tearDown(): void {
		parent::tearDown();
		$this->donotrocketoptimize = false;
	}

	public function testShouldReturnOriginalWhenDONOTROCKETOPTIMIZE() {
		$this->donotrocketoptimize = true;

		$this->assertSame(
			'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css',
			$this->subscriber->maybe_replace_url( 'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css', [ 'all' ] )
		);
	}

	public function testShouldReturnOriginalWhenCDNDisabled() {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn_type', 'rocketcdn' )
			->andReturn( 'rocketcdn' );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( false );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( true );

		$this->assertSame(
			'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css',
			$this->subscriber->maybe_replace_url( 'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css', [ 'all' ] )
		);
	}

	public function testShouldReturnOriginalWhenCDNDisabledOnPost() {
		$this->options->shouldReceive( 'get' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( true );

		Functions\when( 'is_rocket_post_excluded_option' )->justReturn( true );

		$this->assertSame(
			'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css',
			$this->subscriber->maybe_replace_url( 'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css', [ 'all' ] )
		);
	}

	public function testShouldReturnOriginalWhenDriverReturnsFalse() {
		$driver = Mockery::mock( DriverInterface::class );
		$driver->shouldReceive( 'should_rewrite_url' )->andReturn( false );
		$subscription_controller = Mockery::mock( SubscriptionController::class );

		$this->subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			Mockery::mock( Options::class ),
			$subscription_controller,
			Mockery::mock( Cache::class ),
			$this->query,
			$driver
		);

		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( true );

		$this->options->shouldReceive( 'get' )
			->with( 'cdn_type', 'rocketcdn' )
			->andReturn( 'rocketcdn' );

		$subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( true );

		$this->setupDriverGatingMocks();

		$this->assertSame(
			'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css',
			$this->subscriber->maybe_replace_url( 'https://123456.rocketcdn.me/wordpress/wp-content/plugins/hello-dolly/style.css', [ 'all' ] )
		);
	}

	/**
	 * Sets up common WordPress function mocks needed when testing driver-based gating.
	 */
	private function setupDriverGatingMocks(): void {
		Functions\when( 'rocket_get_constant' )->justReturn( false );
		Functions\when( 'is_rocket_post_excluded_option' )->justReturn( false );
		Functions\when( 'home_url' )->justReturn( 'https://example.org' );
		Functions\when( 'add_query_arg' )->justReturn( '' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldMaybeReplaceURL( $config, $expected ) {
		$this->options->shouldReceive( 'get' )
			->andReturn( true );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( true );

		Functions\when( 'is_rocket_post_excluded_option' )->justReturn( false );

		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->zeroOrMoreTimes()
			->andReturn( $config['cdn_urls'] );

			Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
				if ( strpos( $url, 'http://' ) !== false || strpos( $url, 'https://' ) !== false ) {
					return $url;
				}

				if ( substr( $url, 0, 2 ) === '//' ) {
					return 'http:' . $url;
				}

				return 'http://' . $url;
			} );
		Functions\when( 'site_url' )->justReturn( $config['site_url'] );

		$this->assertSame(
			$expected,
			$this->subscriber->maybe_replace_url( $config['original'], $config['zones'] )
		);
	}
}
