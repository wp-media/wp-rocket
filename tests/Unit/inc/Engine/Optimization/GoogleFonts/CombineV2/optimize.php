<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\GoogleFonts\CombineV2;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2::optimize
 *
 * @uses \WP_Rocket\Logger\Logger::info
 * @uses \WP_Rocket\Logger\Logger::debug
 * @uses \WP_Rocket\Engine\Optimization\GoogleFonts\Combine::parse
 * @uses \WP_Rocket\Engine\Optimization\GoogleFonts\Combine::get_combined_url
 * @uses \WP_Rocket\Engine\Optimization\GoogleFonts\Combine::get_optimized_markup
 *
 * @group  Optimize
 * @group  GoogleFonts
 */
class Test_OptimizeV2 extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldCombineV2GoogleFonts( $html, $expected ) {
		Functions\when( 'wp_parse_url' )->alias( function ( $url, $component ) {
			return parse_url( $url, $component );
		} );

		Functions\when( 'wp_parse_args' )->alias( function ( $value ) {
			parse_str( $value, $r );
			return $r;
		} );

		Functions\when( 'esc_url' )->alias( function( $url ) {
			return str_replace( [ '&amp;', '&' ], '&#038;', $url );
		} );

		$combiner = new CombineV2();

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $combiner->optimize( $html ) )
		);
	}
}
