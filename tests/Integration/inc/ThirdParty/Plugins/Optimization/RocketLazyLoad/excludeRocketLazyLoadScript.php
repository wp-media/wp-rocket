<?php
namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering WP_Rocket\ThirdParty\Plugins\Optimization\RocketLazyLoad::exclude_rocket_lazyload_script
 * 
 * @group RocketLazyLoad
 */
class Test_ExcludeRocketLazyLoadScript extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $excluded, $expected ) {
		$this->assertSame( $expected, apply_filters( 'rocket_delay_js_exclusions', $excluded ) );
	}
}
