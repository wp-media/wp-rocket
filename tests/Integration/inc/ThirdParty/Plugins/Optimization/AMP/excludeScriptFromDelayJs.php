<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\AMP;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\Subscriber;
use WP_Rocket\ThirdParty\Plugins\Optimization\AMP;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::exclude_script_from_delay_js
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

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'excludeScriptFromDelayJs' );
	}
}
