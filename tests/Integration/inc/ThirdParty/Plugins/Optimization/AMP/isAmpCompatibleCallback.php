<?php

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Optimization\AMP;

use Brain\Monkey\Functions;
use AMP_Options_Manager;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\AMP::is_amp_compatible_callback
 * @group  ThirdParty
 * @group  WithAmp
 */
class Test_IsAmpCompatibleCallback extends TestCase {
	protected $path_to_test_data = 'isAmpCompatibleCallback.php';

	public function set_up() {
		parent::set_up();

		// Updating the AMP settings will trigger this to run.
		Functions\when( 'rocket_generate_config_file' )->justReturn();
	}

	/**
	 * @dataProvider ampDataProvider
	 */
	public function testShouldReturnExpected( $setting, $expected ) {
		// Set and then check the AMP theme support setting.
		if ( ! is_null( $setting ) ) {
			$this->setSettings( 'theme_support', $setting['theme_support'] );
			$options = get_option( AMP_Options_Manager::OPTION_NAME );
			$this->assertEquals( $setting['theme_support'], $options['theme_support'] );
		} else {
			delete_option( AMP_Options_Manager::OPTION_NAME );
		}

		$this->assertSame( $expected, apply_filters( 'rocket_cache_query_strings', [] ) );
	}
}
