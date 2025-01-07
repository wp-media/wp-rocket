<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\O2Switch;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\O2Switch;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\O2Switch::remove_htaccess_html_expire
 *
 * @group  O2Switch
 * @group  ThirdParty
 */
class Test_RemoveHtaccessHtmlExpire extends TestCase {
	public function setUp() : void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

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
