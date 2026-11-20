<?php

namespace WP_Rocket\Tests\Unit\Inc\Engine\Media\PreconnectExternalDomains\OriginTrait;

use WP_Rocket\Engine\Media\PreconnectExternalDomains\OriginTrait;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering WP_Rocket\Engine\Media\PreconnectExternalDomains\OriginTrait::get_origin
 *
 * @group PreconnectExternalDomains
 */
class Test_GetOrigin extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $url, $expected ) {
		$this->stubWpParseUrl();

		$subject = new class() {
			use OriginTrait;
		};

		$method = $this->get_reflective_method( 'get_origin', get_class( $subject ) );

		$this->assertSame( $expected, $method->invoke( $subject, $url ) );
	}
}
