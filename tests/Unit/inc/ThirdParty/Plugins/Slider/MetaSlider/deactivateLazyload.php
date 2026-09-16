<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Slider\MetaSlider;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Slider\MetaSlider;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Slider\MetaSlider::deactivate_lazyload
 *
 * @group MetaSlider
 * @group ThirdParty
 */
class Test_DeactivateLazyload extends TestCase {
	/**
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected slide attributes.
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->assertSame( $expected, ( new MetaSlider() )->deactivate_lazyload( $config['slide'] ) );
	}
}
