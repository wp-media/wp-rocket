<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::rocket_extract_url_component
 * @group Functions
 */
class Test_RocketExtractUrlComponent extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedUrlComponent( $url, $component, $expected ) {
		$parsed_url = parse_url( $url );
		Functions\expect( 'wp_parse_url' )
			->once()
			->with( $url )
			->andReturn( $parsed_url );
		Functions\expect( '_get_component_from_parsed_url_array' )
			->once()
			->with( $parsed_url, $component )
			->andReturn( $expected );

		$this->assertSame( $expected, rocket_extract_url_component( $url, $component ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rocketExtractUrlComponent' );
	}
}
