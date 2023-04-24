<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Subscriber;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Admin\{Options, Options_Data};
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Subscriber::save_cloudflare_options
 *
 * @group Cloudflare
 */
class TestSaveCloudflareOptions extends TestCase {
	private $options_api;
	private $options;
	private $cloudflare;
	private $subscriber;

	protected function setUp(): void {
		$this->stubTranslationFunctions();

		$this->options_api = Mockery::mock( Options::class );
		$this->options     = Mockery::mock( Options_Data::class );
		$this->cloudflare  = Mockery::mock( Cloudflare::class );
		$this->subscriber  = new Subscriber( $this->cloudflare, $this->options, $this->options_api );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )
			->justReturn( $config['cap'] );

		Functions\when( 'get_transient' )
			->justReturn( $config['transient'] );

		Functions\when( 'is_wp_error' )
			->justReturn( $config['error'] );

		Functions\when( 'get_current_user_id' )
			->justReturn( 1 );

		if ( false === $config['transient'] ) {
			$this->cloudflare->shouldReceive( 'is_auth_valid' )
			->with( $config['value']['cloudflare_zone_id'] )
			->atMost()
			->once()
			->andReturn( $config['auth_valid'] );

			Functions\expect( 'set_transient' )
				->with( 'rocket_cloudflare_is_api_keys_valid', Mockery::type( 'bool'), Mockery::type( 'int') )
				->once();
		}

		if ( null === $expected ) {
			Functions\expect( 'set_transient' )
				->never();
		}

		if ( 'error' === $expected ) {
			Functions\expect( 'add_settings_error' )
				->once();

			Functions\expect( 'set_transient' )
				->with( '1_cloudflare_update_settings', [] )
				->once();
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
			->with( 14400 )
			->atMost()
			->once()
			->andReturn( 14400 );

		if ( is_array( $expected ) ) {
			Functions\expect( 'set_transient' )
				->with( '1_cloudflare_update_settings', Mockery::type( 'array ') )
				->once();
		}

		$this->subscriber->save_cloudflare_options( $config['old_value'], $config['value'] );
	}
}
