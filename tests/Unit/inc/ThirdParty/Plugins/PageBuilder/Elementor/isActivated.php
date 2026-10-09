<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\Elementor;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PageBuilder\Elementor::is_activated
 *
 * @group Elementor
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests Elementor::is_activated() against the presence/absence of ELEMENTOR_VERSION.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['elementor_version'] ) {
			$this->constants['ELEMENTOR_VERSION'] = $config['elementor_version'];
		}

		$this->assertSame( $expected, Elementor::is_activated() );
	}
}
