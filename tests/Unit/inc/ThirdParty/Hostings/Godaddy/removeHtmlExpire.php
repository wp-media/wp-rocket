<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\GoDaddy;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Godaddy;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Godaddy::remove_html_expire
 *
 * @group  Godaddy
 * @group  ThirdParty
 */
class Test_RemoveHtmlExpire extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $htaccess_rules, $expected ) {
		$godaddy = new Godaddy();

		$this->assertSame(
			$expected,
			$godaddy->remove_html_expire( $htaccess_rules )
		);
	}
}
