<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\OptionSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\OptionSubscriber::add_options
 *
 * @group  RUCSS
 */
class Test_AddOptionsFirstTime extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_first_install_options', 'add_options_first_time' );
	}

	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook( 'rocket_first_install_options' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpectedForFirstInstallOptions( $input, $expected ) {
		$options = isset( $input['options'] ) ? $input['options'] : [];

		$actual = apply_filters( 'rocket_first_install_options', $options );

		$this->assertSame( $expected, $actual );
	}
}
