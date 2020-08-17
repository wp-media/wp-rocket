<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Engine\Optimization\GoogleFonts\Combine;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts::optimize
 * @uses   \WP_Rocket\Logger\Logger
 * @group  Optimize
 * @group  GoogleFonts
 */
class Test_Optimize extends TestCase {

	/**
     * @dataProvider addDataProvider
     */
	public function testShouldCombineGoogleFonts( $original, $combined ) {
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
