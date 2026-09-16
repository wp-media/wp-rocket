<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\Slider\MetaSlider;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\Slider\MetaSlider;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Slider\MetaSlider::is_activated
 *
 * eval() declares MetaSliderPlugin in the global namespace for the present case;
 * @runInSeparateProcess keeps it isolated. A namespaced class_exists() override is
 * avoided because it would break PHPStan's class-exists narrowing for other guards.
 *
 * @group MetaSlider
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests is_activated() against the presence/absence of the MetaSliderPlugin class.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_metaslider'] ) {
			eval( 'class MetaSliderPlugin {}' );
		}

		$this->assertSame( $expected, MetaSlider::is_activated() );
	}
}
