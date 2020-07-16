<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Engine\Optimization\GoogleFonts\Optimize;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts::optimize
 * @uses   \WP_Rocket\Logger\Logger
 * @group  Optimize
 */
class Test_Optimize extends TestCase {
	protected static $container;

	/**
     * @dataProvider addDataProvider
     */
	public function testShouldCombineGoogleFonts( $original, $combined ) {
		$combine = new Optimize();

		$this->assertSame(
			$this->format_the_html( $combined ),
			$this->format_the_html( $combine->optimize( $original ) )
		);
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'optimize' );
    }
}
