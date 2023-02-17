<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\WPXCloud;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\WPXCloud;

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
		$wpx_cloud = new WPXCloud();

		$this->assertSame(
			$expected,
			$wpx_cloud->remove_htaccess_html_expire( $htaccess_rules )
		);
	}
}
