<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::rocket_add_url_protocol
 * @group  Functions
 * @group  Formatting
 */
class Test_RocketAddUrlProtocol extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedParsedUrl( $url, $expected ) {
		if ( strpos( $url, 'http' ) === false ) {
			Functions\expect( 'set_url_scheme' )
				->once()
				->andReturnUsing(
					function ( $url ) {
						return "http:{$url}";
					}
				);
		} else {
			Functions\expect( 'set_url_scheme' )->never();
		}
		$this->assertSame( $expected, rocket_add_url_protocol( $url ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rocketAddUrlProtocol' );
	}
}
