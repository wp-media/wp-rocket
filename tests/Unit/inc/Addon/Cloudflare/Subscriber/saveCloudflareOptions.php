<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;
use WPMedia\Cloudflare\Auth\AuthFactoryInterface;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Subscriber::save_cloudflare_options
 *
 * @group Cloudflare
 */
class TestSaveCloudflareOptions extends TestCase {
	private $options_api;
	private $options;
	private $cloudflare;

	private $factory;

	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->stubTranslationFunctions();

		$this->options_api = Mockery::mock( Options::class );
		$this->options     = Mockery::mock( Options_Data::class );
		$this->cloudflare  = Mockery::mock( Cloudflare::class );
		$this->factory = Mockery::mock( AuthFactoryInterface::class );
		$this->subscriber  = new Subscriber( $this->cloudflare, $this->options, $this->options_api, $this->factory );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )
			->justReturn( $config['cap'] );

		Functions\expect( 'is_wp_error' )
			->atMost()
			->once()
			->andReturn( $config['connection'] )
			->andAlsoExpectIt()
			->andReturn( $config['error'] );

		Functions\when( 'get_current_user_id' )
			->justReturn( 1 );

		$this->cloudflare->shouldReceive( 'check_connection' )
			->andReturn( $config['transient'] );

		if ( null === $expected ) {
			Functions\expect( 'set_transient' )
				->never();
		}

		$this->cloudflare->shouldReceive( 'set_cache_level' )
			->with( 'aggressive' )
			->atMost()
			->once()
			->andReturn( 'aggressive' );

		$this->cloudflare->shouldReceive( 'set_minify' )
			->with( 'on' )
			->atMost()
			->once()
			->andReturn( 'on' );

		$this->cloudflare->shouldReceive( 'set_rocket_loader' )
			->with( 'off' )
			->atMost()
			->once()
			->andReturn( 'off' );

		$this->cloudflare->shouldReceive( 'set_browser_cache_ttl' )
			->with( 31536000 )
			->atMost()
			->once()
			->andReturn( 31536000 );

		if ( is_array( $expected ) ) {
			Functions\expect( 'set_transient' )
				->with( '1_cloudflare_update_settings', Mockery::type( 'array ') )
				->once();
		}

		$this->subscriber->save_cloudflare_options( $config['old_value'], $config['value'] );
	}
}
