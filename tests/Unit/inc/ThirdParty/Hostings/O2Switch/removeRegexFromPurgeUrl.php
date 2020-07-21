<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\O2Switch;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\O2Switch;

/**
 * @covers \WP_Rocket\ThirdParty\Hostings\O2Switch::remove_regex_from_purge_url
 *
 * @group  O2Switch
 * @group  ThirdParty
 */
class Test_RemoveRegexFromPurgeUrl extends TestCase {
	protected static $mockCommonWpFunctionsInSetUp = true;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ) {
		$full_purge_url = isset( $input['full_purge_url'] ) ? $input['full_purge_url'] : '' ;
		$main_purge_url = isset( $input['main_purge_url'] ) ? $input['main_purge_url'] : '' ;

		$o2switch = new O2Switch();

		$this->assertSame(
			$expected,
			$o2switch->remove_regex_from_purge_url( $full_purge_url, $main_purge_url )
		);
	}
}
