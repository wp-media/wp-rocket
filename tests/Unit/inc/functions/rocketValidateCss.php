<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_validate_css
 *
 * @group Functions
 * @group Formatting
 */
class Test_RocketValidateCss extends TestCase {
	public function setUp() : void {
		parent::setUp();

		Functions\stubEscapeFunctions();
	}

	/**
	 * @dataProvider addProvider
	 */
	public function testShouldValidateCSS( $url, $validated ) {
		Functions\when( 'rocket_is_internal_file' )->alias( function( $url ) {
			return 'example.org' === parse_url( $url, PHP_URL_HOST );
		} );
		Functions\when( 'rocket_clean_exclude_file' )->alias( function( $url ) {
			if ( ! $url ) {
				return false;
			}
			return parse_url( $url, PHP_URL_PATH );
		} );

		Functions\when( 'rocket_remove_url_protocol' )->alias( function( $url ) {
			return str_replace( [ 'http://', 'https://' ], '', $url );
		} );

		$this->assertSame( $validated, rocket_validate_css( $url ) );
	}

	public function addProvider() {
		return $this->getTestData( __DIR__, 'rocketValidateCss' );
	}
}
