<?php

namespace WP_Rocket\Tests\Integration\inc\functions;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::get_rocket_parse_url
 * @group  Functions
 * @group  Posts
 */
class Test_GetRocketParseUrls extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedParsedUrl( $url, $expected ) {
		$this->assertSame( $expected, get_rocket_parse_url( $url ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'getRocketParseUrl' );
	}
}
