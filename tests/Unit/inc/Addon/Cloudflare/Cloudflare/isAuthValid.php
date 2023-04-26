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
 * @covers WP_Rocket\Addon\Cloudflare\Cloudflare::is_auth_valid
 *
 * @group Cloudflare
 */
class TestIsAuthValid extends TestCase {
	private $options;
	private $endpoints;
	private $cloudflare;

	protected function setUp(): void {
		$this->stubTranslationFunctions();
		$this->stubEscapeFunctions();

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
		$this->endpoints->shouldReceive( 'get_zones' )
			->with( $config['zone_id'] )
			->atMost()
			->once()
			->andReturn( $config['response'] );

		Functions\when( 'is_wp_error' )
			->justReturn( $config['request_error'] );

		Functions\when( 'get_site_url' )
			->justReturn( 'http://example.org' );

		$this->stubWpParseUrl();

		$result = $this->cloudflare->is_auth_valid( $config['zone_id'] );

		if ( 'error' === $expected ) {
			$this->assertInstanceOf(
				WP_Error::class,
				$result
			);
		} else {
			$this->assertTrue( $result );
		}
	}
}
