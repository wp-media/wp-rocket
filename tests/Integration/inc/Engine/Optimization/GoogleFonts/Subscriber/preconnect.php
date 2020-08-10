<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber::preconnect
 * @group CombineGoogleFonts
 */
class Test_Preconnect extends TestCase {
	private $option_value;

	public function setUp() {
		parent::setUp();

		$this->option_value = null;
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'set_option' ] );

		unset( $GLOBALS['wp'] );

		parent::tearDown();
	}

    /**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedArray( $bypass, $option_value, $urls, $relation_type, $expected ) {
		if ( $bypass ) {
			$GLOBALS['wp'] = (object) [
				'query_vars' => [
					'nowprocket' => 1,
				],
				'request'    => 'http://example.org',
			];
		}

		$this->option_value = $option_value;

		add_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'set_option' ] );

		$this->assertSame(
			$expected,
			apply_filters( 'wp_resource_hints', $urls, $relation_type )
        );   
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preconnect' );
	}

	public function set_option() {
		return $this->option_value;
	}
}