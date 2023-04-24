<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Cloudflare;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Addon\Cloudflare\Cloudflare;
use WP_Rocket\Addon\Cloudflare\API\Endpoints;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Cloudflare::get_cloudflare_ips
 *
 * @group Cloudflare
 */
class TestGetCloudflareIps extends TestCase {
	private $options;
	private $endpoints;
	private $cloudflare;

	protected function setUp(): void {
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
		Functions\when( 'get_transient' )
			->justReturn( $config['transient'] );

		if ( 'exception' === $config['response'] ) {
			$this->endpoints->expects()
				->get_ips()
				->andThrow( new \Exception() );
		} else {
			$this->endpoints->shouldReceive( 'get_ips' )
				->atMost()
				->once()
				->andReturn( $config['response'] );
		}

		if ( false === $config['transient'] ) {
			Functions\expect( 'set_transient' )
				->atMost()
				->once();
		}

		$result = $this->cloudflare->get_cloudflare_ips();

		$this->assertEquals(
			$expected,
			$result
		);
	}
}
