<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::maybe_disable_combine_js
 *
 * @group DelayJS
 * @group AdminOnly
 */
class Test_MaybeDisableCombineJs extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'pre_update_option_wp_rocket_settings', 'maybe_disable_combine_js', 11 );
	}

	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook( 'pre_update_option_wp_rocket_settings' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpectedForFirstInstallOptions( $config, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'pre_update_option_wp_rocket_settings', $config['value'], $config['old_value'] )
		);
	}
}
