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
	protected $options;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		self::$container = apply_filters( 'rocket_container', null );
	}

	public function setUp() {
		parent::setUp();

		$this->options = self::$container->get( 'options' );
	}

	/**
     * @dataProvider addDataProvider
     */
	public function testShouldCombineGoogleFonts( $original, $combined ) {
		$combine = new Optimize( $this->options );

		$this->assertSame(
			$this->format_the_html( $combined ),
			$this->format_the_html( $combine->optimize( $original ) )
		);
	}

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'optimize' );
    }
}
