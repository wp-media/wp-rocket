<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DeferJS\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\AdminSubscriber::add_defer_js_option
 *
 * @group  AdminOnly
 * @group  DeferJS
 */
class Test_AddDeferJsOption extends TestCase {
	public function setUp() : void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_first_install_options', 'add_defer_js_option' );
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
