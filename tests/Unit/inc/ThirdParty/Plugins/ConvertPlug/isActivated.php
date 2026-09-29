<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\ConvertPlug;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\ConvertPlug;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\ConvertPlug::is_activated
 *
 * @group ConvertPlug
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests ConvertPlug::is_activated() against the presence/absence of CP_VERSION.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['cp_version'] ) {
			$this->constants['CP_VERSION'] = $config['cp_version'];
		}

		$this->assertSame( $expected, ConvertPlug::is_activated() );
	}
}
