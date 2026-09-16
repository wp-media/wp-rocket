<?php
namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Plugins\PageBuilder\VisualComposer;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Plugins\PageBuilder\VisualComposer;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\PageBuilder\VisualComposer::is_activated
 *
 * WPB_VC_VERSION is mocked through the rocket_has_constant() stub ($this->constants);
 * class_exists( 'Vc_Manager' ) is exercised via eval() under @runInSeparateProcess so the
 * declared class stays isolated to its own process.
 *
 * @group VisualComposer
 * @group ThirdParty
 */
class Test_IsActivated extends TestCase {
	/**
	 * Tests is_activated() against the WPB_VC_VERSION constant and Vc_Manager class.
	 *
	 * @runInSeparateProcess
	 * @preserveGlobalState disabled
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected return value.
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		if ( $config['define_version'] ) {
			$this->constants['WPB_VC_VERSION'] = '7.0';
		}

		if ( $config['define_manager'] ) {
			eval( 'class Vc_Manager {}' );
		}

		$this->assertSame( $expected, VisualComposer::is_activated() );
	}
}
