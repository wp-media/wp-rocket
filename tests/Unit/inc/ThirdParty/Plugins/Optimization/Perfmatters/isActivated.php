<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\Perfmatters;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\Perfmatters::is_activated
 *
 * @group Perfmatters
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests Perfmatters::is_activated() against the presence/absence of PERFMATTERS_VERSION.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['perfmatters_version'] ) {
			$this->constants['PERFMATTERS_VERSION'] = $config['perfmatters_version'];
		}

		$this->assertSame( $expected, Perfmatters::is_activated() );
	}
}
