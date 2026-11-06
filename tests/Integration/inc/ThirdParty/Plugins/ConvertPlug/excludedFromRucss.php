<?php

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering  \WP_Rocket\ThirdParty\Plugins\ConvertPlug::excluded_from_rucss
 * @group   ConvertPlug
 */
class Test_ExcludedFromRucss extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->assertSame( $expected, apply_filters( 'rocket_rucss_inline_atts_exclusions', $config['excluded'] ));
	}

}
