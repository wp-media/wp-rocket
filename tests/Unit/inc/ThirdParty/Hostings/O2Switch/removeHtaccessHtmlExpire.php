<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\O2Switch;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\O2Switch;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\O2Switch::remove_htaccess_html_expire
 *
 * @group  O2Switch
 * @group  ThirdParty
 */
class Test_RemoveHtaccessHtmlExpire extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $htaccess, $expected ) {
		$o2switch = new O2Switch();

		$this->assertSame(
			$expected,
			$o2switch->remove_htaccess_html_expire( $htaccess )
		);
	}
}
