<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::rocket_add_url_protocol
 * @group  Functions
 * @group  Formatting
 */
class Test_RocketAddUrlProtocol extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedParsedUrl( $url, $expected ) {
		$this->assertSame( $expected, rocket_add_url_protocol( $url ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rocketAddUrlProtocol' );
	}
}
