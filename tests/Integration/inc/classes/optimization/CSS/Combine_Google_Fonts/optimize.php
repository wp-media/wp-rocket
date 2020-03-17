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

	/**
     * @dataProvider addDataProvider
     */
	public function testShouldCombineGoogleFonts( $original, $combined ) {
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
