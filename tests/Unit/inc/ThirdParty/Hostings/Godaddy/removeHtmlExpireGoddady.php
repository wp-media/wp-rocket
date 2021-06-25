<?php
/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Godaddy::godaddy_varnish_field
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Godaddy;

class Test_removeHtmlExpireGoddady extends TestCase {

	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $htaccess_rules, $expected ) {

		$vip_url='vip-url.com';
		$godaddy = new Godaddy($vip_url);

		$this->assertSame(
			$expected,
			$godaddy->remove_html_expire_goddady( $htaccess_rules )
		);
	}
}
