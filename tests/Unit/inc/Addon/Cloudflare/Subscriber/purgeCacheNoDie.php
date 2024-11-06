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
 * Test class covering WP_Rocket\Addon\Cloudflare\Subscriber::purge_cache_no_die
 *
 * @group Cloudflare
 */
class TestPurgeCacheNoDie extends TestCase {
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
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )
			->justReturn( $config['cap'] );

		Functions\expect( 'is_wp_error' )
			->atMost()
			->once()
			->andReturn( $config['connection'] )
			->andAlsoExpectIt()
			->atMost()
			->once()
			->andReturn( $config['error'] );

		$this->cloudflare->shouldReceive( 'check_connection' )
			->andReturn( true );

		$this->cloudflare->shouldReceive( 'purge_cloudflare' )
			->atMost()
			->once()
			->andReturn( $config['result'] );

		Functions\when( 'get_current_user_id' )
			->justReturn( 1 );

		if ( null === $expected ) {
			Functions\expect( 'set_transient' )
				->never();
		} else {
			Functions\expect( 'set_transient' )
				->once();
		}

		$this->subscriber->purge_cache_no_die();
	}
}
