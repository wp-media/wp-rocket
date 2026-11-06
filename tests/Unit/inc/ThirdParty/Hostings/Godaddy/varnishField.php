<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\GoDaddy;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Godaddy;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Godaddy::varnish_field
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
class Test_VarnishField extends TestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $settings, $expected ) {
		$godaddy = new Godaddy();

		$this->assertSame(
			$expected,
			$godaddy->varnish_field( $settings )
		);
	}
}
