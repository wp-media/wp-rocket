<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\Hummingbird;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\Hummingbird::is_activated
 *
 * is_admin() must gate is_activated() to preserve the pre-refactor admin-only
 * construction timing (issue #8790 slice 3, user decision).
 *
 * @group ThirdParty
 * @group Hummingbird
 */
class Test_isActivated extends TestCase {

	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Functions\when( 'is_admin' )->justReturn( $config['is_admin'] );
		Functions\when( 'is_plugin_active' )->justReturn( $config['plugin_active'] );

		$this->assertSame( $expected, Hummingbird::is_activated() );
	}
}
