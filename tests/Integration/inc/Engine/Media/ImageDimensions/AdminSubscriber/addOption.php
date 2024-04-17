<?php

namespace WP_Rocket\Tests\Integration\inc\Media\ImageDimensions\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\ImageDimensions\AdminSubscriber::add_option
 *
 * @group  AdminOnly
 * @group  ImageDimensions
 */
class Test_AddOption extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_first_install_options', 'add_option', 14 );
	}

	public function tear_down() {
		parent::tear_down();

		$this->restoreWpHook( 'rocket_first_install_options' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpectedForFirstInstallOptions( $input, $expected ) {
		$options = isset( $input['options'] )  ? $input['options']  : [];

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_first_install_options', $options )
		);

	}
}
