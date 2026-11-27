<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Optimole;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Optimole;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Optimole::is_activated
 *
 * @group Optimole
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests Optimole::is_activated() against the presence/absence of OPTML_VERSION.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['optml_version'] ) {
			$this->constants['OPTML_VERSION'] = $config['optml_version'];
		}

		$this->assertSame( $expected, Optimole::is_activated() );
	}
}
