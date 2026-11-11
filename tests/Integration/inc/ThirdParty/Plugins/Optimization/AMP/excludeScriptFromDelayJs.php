<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::exclude_script_from_delay_js
 * @group  ThirdParty
 * @group  WithAmp
 */
class Test_ExcludeScriptFromDelayJs extends TestCase
{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldExcludeScriptWhenAmpPresent($config, $expected) {
		$this->assertEquals($expected['excluded'], apply_filters( 'rocket_delay_js_exclusions', $config['excluded']));
	}

}
