<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;
/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Godaddy::godaddy_varnish_field
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Godaddy;

class Test_godaddyVarnishField extends TestCase {

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
			$godaddy->godaddy_varnish_field( $settings )
		);
	}
}
