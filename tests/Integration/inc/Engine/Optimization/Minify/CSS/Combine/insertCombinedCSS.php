<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\Minify\CSS\Combine;

use MatthiasMullie\Minify\CSS;
use WP_Rocket\Engine\Optimization\Minify\CSS\Combine;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\Combine::insert_combined_css
 * @group Optimize
 */
class Test_InsertCombinedCSS extends TestCase {

	public function testShouldInsertCombinedCSS() {
		$combine      = new Combine( new Options_Data( ( new Options( 'wp_rocket_' ) )->get( 'settings' ) ), new CSS() );
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
