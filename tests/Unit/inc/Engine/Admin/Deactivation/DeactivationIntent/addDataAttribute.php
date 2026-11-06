<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Deactivation\DeactivationIntent;

use Mockery;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::add_data_attribute
 * @group  DeactivationIntent
 */
class Test_AddDataAttribute extends TestCase {
	private $deactivation;
	private $options;
	private $options_api;

	public function setUp() : void {
		parent::setUp();

		$this->options_api  = Mockery::mock( 'WP_Rocket\Admin\Options' );
		$this->options      = Mockery::mock( 'WP_Rocket\Admin\Options_Data' );
		$this->deactivation = new DeactivationIntent( '', $this->options_api, $this->options );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $actions, $expected ) {
		$this->assertSame(
			$expected,
			$this->deactivation->add_data_attribute( $actions )
		);
	}
}
