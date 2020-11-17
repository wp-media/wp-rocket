<?php

namespace WP_Rocket\Tests\Integration\inc\Media\ImagesDimensions\AdminSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Media\ImagesDimensions\AdminSubscriber::add_option
 *
 * @group  AdminOnly
 * @group  ImagesDimensions
 */
class Test_AddOption extends TestCase {
	public function setUp() {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'rocket_first_install_options', 'add_option', 14 );
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
