<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * Test class covering ::rocket_extract_url_component
 * @group Functions
 */
class Test_RocketExtractUrlComponent extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedUrlComponent( $url, $component, $expected ) {
		$this->assertSame( $expected, rocket_extract_url_component( $url, $component ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rocketExtractUrlComponent' );
	}
}
