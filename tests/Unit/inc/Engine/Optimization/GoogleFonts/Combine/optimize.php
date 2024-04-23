<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\GoogleFonts\Combine;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Engine\Optimization\GoogleFonts\Combine;
use WP_Rocket\Engine\Optimization\GoogleFonts\AbstractGFOptimization;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\GoogleFonts\Combine::optimize
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
class Test_Optimize extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldCombineGoogleFonts( $html, $expected, $filtered = false ) {
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

		if ( false !== $filtered ) {
			Filters\expectApplied('rocket_combined_google_fonts_display')
				->with('swap', Mockery::type(AbstractGFOptimization::class))
				->andReturn( $filtered );
		}

		$combine = new Combine();

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $combine->optimize( $html ) )
		);
	}
}
