<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Hostings\Dreampress;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Dreampress::remove_htaccess_html_expire
 *
 * @group  Dreampress
 * @group  ThirdParty
 */
class Test_RemoveHtaccessHtmlExpire extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $rules, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'rocket_htaccess_mod_expires', $rules )
		);
	}
}
