<?php

namespace WP_Rocket\Tests\Integration\inc\optimization\CSS\Combine_Google_Fonts;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Optimization\CSS\Combine_Google_Fonts;

/**
 * @covers \WP_Rocket\Optimization\CSS\Combine_Google_Fonts::optimize
 * @uses   \WP_Rocket\Logger\Logger
 * @group  Optimize
 */
class Test_Optimize extends TestCase {

	public function testShouldCombineGoogleFontsWhenSubset() {
		$combine = new Combine_Google_Fonts();

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/GoogleFonts/original-subset.html' );
		$combined = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/GoogleFonts/combined-subset.html' );

		$this->assertSame(
			$combined,
			$combine->optimize( $original )
		);
	}

	public function testShouldCombineGoogleFontsWhenNoSubset() {
		$combine = new Combine_Google_Fonts();

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/GoogleFonts/original.html' );
		$combined = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/GoogleFonts/combined.html' );

		$this->assertSame(
			$combined,
			$combine->optimize( $original )
		);
	}
}
