<?php

namespace WP_Rocket\Tests\Integration\Inc\ThirdParty\Plugins\Optimization\WPMeteor;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor::maybe_disable_delay_js_field
 *
 * @group WPMeteor
 * @group ThirdParty
 */
class Test_MaybeDisableDelayJsField extends TestCase {
	private $plugin_active;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $field, $expected ) {
		$this->plugin_active = $config['plugin_active'];

		add_filter( 'pre_option_active_plugins', [ $this, 'active_plugin' ] );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_delay_js_settings_field', $field )
		);
	}

	public function active_plugin( $plugins ) {
		if ( ! $this->plugin_active ) {
			return $plugins;
		}

		$plugins[] = 'wp-meteor/wp-meteor.php';

		return $plugins;
	}
}
