<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::get_rocket_parse_url
 * @group  Functions
 * @group  Posts
 */
class Test_GetRocketParseUrls extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedParsedUrl( $url, $expected ) {
		if ( is_string( $url ) ) {
			Functions\expect( 'wp_parse_url' )
				->once()
				->with( $url )
				->andReturnUsing( function( $url ) {
					return parse_url( $url );
			} );
			Filters\expectApplied( 'rocket_parse_url' )
				->once()
				->with( $expected )
				->andReturn( $expected );
		} else {
			Functions\expect( 'wp_parse_url' )->never();
			Filters\expectApplied( 'rocket_parse_url' )->never();
		}

		$this->assertSame( $expected, get_rocket_parse_url( $url ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'getRocketParseUrl' );
	}
}
