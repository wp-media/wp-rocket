<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\GoogleFonts;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2;
use WP_Rocket\Logger\Logger;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\CombineV2::optimize
 *
 * @group  Optimize
 * @group  GoogleFonts
 *
 * @uses   Logger::info
 * @uses   Logger::debug
 */
class Test_OptimizeV2 extends TestCase {

	/**
	 * @dataProvider provide
	 */
	public function testShouldCombineV2GoogleFonts( $original, $expected ) {
		Functions\when( 'wp_parse_url' )->alias( function ( $url, $component ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'wp_parse_args' )->alias( function ( $value ) {
			parse_str( $value, $r );

			return $r;
		} );
		Functions\when( 'esc_url' )->returnArg();

		$expected = $this->format_the_html($expected);
		$combiner = new CombineV2();

		$actual = $this->format_the_html($combiner->optimize($original));

		$this->assertEquals($expected, $actual);
	}

	public function provide() {
		return $this->getTestData( __DIR__, 'optimize' );
	}
}
