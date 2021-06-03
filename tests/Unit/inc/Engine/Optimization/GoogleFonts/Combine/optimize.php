<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\GoogleFonts\Combine;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Optimization\GoogleFonts\Combine;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Combine::optimize
 * @group  Optimize
 * @group  GoogleFonts
 */
class Test_Optimize extends TestCase {

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldCombineGoogleFonts( $original, $combined ) {
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'wp_parse_args' )->alias( function( $value ) {
			parse_str( $value, $r );

			return $r;
		} );
		Functions\when( 'esc_url' )->alias( function( $url ) {
			return str_replace( [ '&amp;', '&' ], '&#038;', $url );
		} );

		$combine = new Combine();

		$this->assertSame(
			$combined,
			$combine->optimize( $original )
		);
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'optimize' );
	}
}
