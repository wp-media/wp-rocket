<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Cloudflare;

use Brain\Monkey\Functions;
use Mockery;
use WP_Error;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Addon\Cloudflare\API\Endpoints;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Cloudflare::set_dev_mode
 *
 * @group Cloudflare
 */
class TestSetDevMode extends TestCase {
	private $options;
	private $endpoints;
	private $cloudflare;

	protected function setUp(): void {
		parent::setUp();

		Functions\when( 'get_transient' )
			->justReturn( true );

		$this->options    = Mockery::mock( Options_Data::class );
		$this->endpoints  = Mockery::mock( Endpoints::class );
		$this->cloudflare = new Cloudflare( $this->options, $this->endpoints );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		Functions\expect( 'is_wp_error' )
			->once()
			->andReturn( false )
			->andAlsoExpectIt()
			->once()
			->andReturn( $config['request_error'] );

		$this->options->expects()
			->get( 'cloudflare_zone_id', '' )
			->andReturn( $config['zone_id'] );

		$this->endpoints->shouldReceive( 'change_development_mode' )
			->with( $config['zone_id'], $config['setting'] )
			->atMost()
			->once()
			->andReturn( $config['response'] );

		if ( 1 === $config['value'] ) {
			Functions\expect( 'wp_schedule_single_event' )
				->once();
		}

		$result = $this->cloudflare->set_devmode( $config['value'] );

		if ( 'error' === $expected ) {
			$this->assertInstanceOf(
				WP_Error::class,
				$result
			);
		} else {
			$this->assertSame(
				$expected,
				$result
			);
		}
	}
}
