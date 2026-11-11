<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimization\RocketLazyLoad;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimization\RocketLazyLoad;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimization\RocketLazyLoad::is_activated
 *
 * @group RocketLazyLoad
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests RocketLazyLoad::is_activated() against the presence/absence of ROCKET_LL_VERSION.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['rocket_ll_version'] ) {
			$this->constants['ROCKET_LL_VERSION'] = $config['rocket_ll_version'];
		}

		$this->assertSame( $expected, RocketLazyLoad::is_activated() );
	}
}
