<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\RevolutionSlider;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\RevolutionSlider;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\RevolutionSlider::is_activated
 *
 * @group RevolutionSlider
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests RevolutionSlider::is_activated() against RS_REVISION presence/version.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['rs_revision'] ) {
			define( 'RS_REVISION', $config['rs_revision'] );
		}

		$this->assertSame( $expected, RevolutionSlider::is_activated() );
	}
}
