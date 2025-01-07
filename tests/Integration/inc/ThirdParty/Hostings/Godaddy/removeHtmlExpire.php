<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Godaddy;

use WP_Rocket\Tests\Integration\TestCase;

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
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_htaccess_mod_expires', $htaccess_rules )
		);
	}
}
