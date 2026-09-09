<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Cookie\Termly;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Cookie\Termly;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Cookie\Termly::is_activated
 *
 * @group Termly
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests Termly::is_activated() against the presence/absence of TERMLY_VERSION.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['termly_version'] ) {
			$this->constants['TERMLY_VERSION'] = $config['termly_version'];
		}

		$this->assertSame( $expected, Termly::is_activated() );
	}
}
