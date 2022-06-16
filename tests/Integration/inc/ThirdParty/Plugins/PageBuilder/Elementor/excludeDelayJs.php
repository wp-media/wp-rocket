<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::exclude_delay_js
 * @group Elementor
 * @group ThirdParty
 */
class Test_ExcludeDelayJs extends TestCase
{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_delay_js_exclusions', $config['excluded'] )
		);
	}
}
