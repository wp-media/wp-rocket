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

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Godaddy::remove_html_expire_goddady()
 * @uses   ::rocket_get_constant
 *
 * @group  Godaddy
 * @group  ThirdParty
 */

class Test_removeHtmlExpireGoddady extends TestCase {

	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $htaccess_rules, $expected ) {

		$godaddy = new Godaddy();

		$this->assertSame(
			$expected,
			$godaddy->remove_html_expire_goddady( $htaccess_rules )
		);
	}
}
