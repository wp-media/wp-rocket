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
 * Test class covering WP_Rocket\Addon\Cloudflare\Cloudflare::set_dev_mode
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
			->andReturn( $config['request_error'] );

		$this->options->expects()
			->get( 'cloudflare_zone_id', '' )
			->andReturn( $config['zone_id'] );

		if ( is_array( $config['response'] ) && isset( $config['response']['body'] ) ) {
			$body = json_decode( $config['response']['body'] );
			$response = $body->result;
		} else {
			$response = $config['response'];
		}

		$this->endpoints->shouldReceive( 'change_development_mode' )
			->with( $config['zone_id'], $config['setting'] )
			->atMost()
			->once()
			->andReturn( $response );


		if( 'error' !== $expected) {
			if ( 1 === $config['value'] ) {
				Functions\expect( 'wp_schedule_single_event' )
					->once();
			} else {
				Functions\when( 'wp_next_scheduled' )
					->justReturn( 12345 );
				Functions\expect( 'wp_unschedule_event' )
					->once();
			}
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
