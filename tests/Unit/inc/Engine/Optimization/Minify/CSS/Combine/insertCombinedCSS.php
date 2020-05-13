<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\Combine;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Engine\Optimization\Minify\CSS\Combine;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\Combine::insert_combined_css
 * @group  Optimize
 */
class Test_InsertCombinedCSS extends TestCase {

	public function testShouldInsertCombinedCSS() {
		Functions\when( 'create_rocket_uniqid' )->justReturn( '1234' );
		Functions\when( 'get_current_blog_id' )->justReturn( '1' );

		$options = $this->createMock( 'WP_Rocket\Admin\Options_Data' );
		$minify  = $this->createMock( 'MatthiasMullie\Minify\CSS' );

		$combine      = new Combine( $options, $minify );
		$combined_url = 'combined.css';
		$styles       = [
			[
				'<link rel="stylesheet" href="style.css" />',
			],
			[
				'<link rel="stylesheet" href="plugin.css" />',
			],
		];

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/original.html' );
		$combined = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/combined.html' );

		$this->assertSame(
			$combined,
			$combine->insert_combined_css( $original, $combined_url, $styles )
		);
	}
}
