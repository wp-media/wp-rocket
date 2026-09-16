<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Slider\Soliloquy;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Slider\Soliloquy;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Slider\Soliloquy::deactivate_lazyload
 *
 * @group Soliloquy
 * @group ThirdParty
 */
class Test_DeactivateLazyload extends TestCase {
	/**
	 * @dataProvider configTestData
	 *
	 * @param array  $config   Test configuration.
	 * @param string $expected Expected image attributes.
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->assertSame( $expected, ( new Soliloquy() )->deactivate_lazyload( $config['attr'] ) );
	}
}
