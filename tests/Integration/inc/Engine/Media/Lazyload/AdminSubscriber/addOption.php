<?php

namespace WP_Rocket\Tests\Integration\inc\Media\Lazyload\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\Lazyload\AdminSubscriber::add_option
 *
 * @group  AdminOnly
 * @group  Lazyload
 */
class Test_AddOption extends TestCase {
	public function setUp() : void {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_first_install_options', 'add_option', 15 );
	}

	public function tearDown() {
		parent::tearDown();

		$this->restoreWpFilter( 'rocket_first_install_options' );
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
