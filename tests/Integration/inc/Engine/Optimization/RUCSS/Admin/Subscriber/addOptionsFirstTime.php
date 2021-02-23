<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\RUCSS\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Subscriber::add_options
 *
 * @group  RUCSS
 */
class Test_AddOptionsFirstTime extends TestCase {
	public function setUp() {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_first_install_options', 'add_options_first_time' );
	}

	public function tearDown() {
		parent::tearDown();

		$this->restoreWpFilter( 'rocket_first_install_options' );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpectedForFirstInstallOptions( $input, $expected ) {
		$options  = isset( $input['options'] )  ? $input['options']  : [];

		$actual = apply_filters( 'rocket_first_install_options', $options );

		$this->assertSame( $expected, $actual );

	}
}
