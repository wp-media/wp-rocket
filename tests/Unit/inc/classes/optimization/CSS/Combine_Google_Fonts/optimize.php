<?php

namespace WP_Rocket\Tests\Unit\inc\optimization\CSS\Combine_Google_Fonts;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Optimization\CSS\Combine_Google_Fonts;

/**
 * @covers \WP_Rocket\Optimization\CSS\Combine_Google_Fonts::optimize
 * @group  Optimize
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

		$combine = new Combine_Google_Fonts();

		$this->assertSame(
			$combined,
			$combine->optimize( $original )
		);
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'optimize' );
	}
}
