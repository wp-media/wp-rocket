<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Dreampress;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Dreampress;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\Dreampress::remove_htaccess_html_expire
 *
 * @group  Dreampress
 * @group  ThirdParty
 */
class Test_RemoveHtaccessHtmlExpire extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $htaccess, $expected ) {
		$dreampress = new Dreampress();

		$this->assertSame(
			$expected,
			$dreampress->remove_htaccess_html_expire( $htaccess )
		);
	}
}
