<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\Autoptimize;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\Autoptimize;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\Autoptimize::is_activated
 *
 * Note: is_activated() extracts only the presence sub-expression from the
 * untouched private can_notify() (which also checks get_current_screen() and
 * current_user_can()) — that business-logic branch stays untested here since
 * it isn't reachable from is_activated().
 *
 * @group Autoptimize
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests Autoptimize::is_activated() against the presence/absence of AUTOPTIMIZE_PLUGIN_VERSION.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['autoptimize_plugin_version'] ) {
			$this->constants['AUTOPTIMIZE_PLUGIN_VERSION'] = $config['autoptimize_plugin_version'];
		}

		$this->assertSame( $expected, Autoptimize::is_activated() );
	}
}
