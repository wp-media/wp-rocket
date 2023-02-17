<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\WPXCloud;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\WPXCloud::remove_htaccess_html_expire
 *
 * @group  WPXCloud
 */
class Test_RemoveHtaccessHtmlExpire extends TestCase {
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
