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
 * @covers WP_Rocket\Addon\Cloudflare\Cloudflare::purge_cloudflare
 *
 * @group Cloudflare
 */
class TestPurgeCloudflare extends TestCase {
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

		$this->endpoints->shouldReceive( 'purge' )
			->with( $config['zone_id'] )
			->atMost()
			->once()
			->andReturn( $response );

		$result = $this->cloudflare->purge_cloudflare();

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
