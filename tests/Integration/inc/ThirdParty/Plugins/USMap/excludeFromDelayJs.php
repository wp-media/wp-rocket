<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\USMap;

use WP_Rocket\Tests\Integration\TestCase;

/**
* @covers \WP_Rocket\ThirdParty\Plugins\USMap::exclude_from_delay_js
* @group ThirdParty
* @group USMap
*/
class Test_ExcludeFromDelayJs extends TestCase {

	/**
	* @dataProvider configTestData
	*/
	public function testShouldDoExpected( $config, $expected ) {
		$this->assertSame(
				$expected,
				apply_filters( 'rocket_delay_js_exclusions', $config )
			);
		}

}
