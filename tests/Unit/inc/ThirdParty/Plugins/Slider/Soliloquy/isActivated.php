<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Slider\Soliloquy;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Slider\Soliloquy;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Slider\Soliloquy::is_activated
 *
 * SOLILOQUY_VERSION is mocked through the rocket_has_constant() stub ($this->constants),
 * so no process isolation is required. The constant is defined by both the free
 * (Soliloquy Lite) and paid (Soliloquy) builds, which is why it is the detection gate.
 *
 * @group Soliloquy
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests is_activated() against the presence/absence of the SOLILOQUY_VERSION constant.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( null !== $config['soliloquy_version'] ) {
			$this->constants['SOLILOQUY_VERSION'] = $config['soliloquy_version'];
		}

		$this->assertSame( $expected, Soliloquy::is_activated() );
	}
}
