<?php

namespace WP_Rocket\Tests\Unit\Inc;

use Brain\Monkey;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::rocket_has_constant
 * @group Init
 * @group Constants
 */
class Test_RocketHasConstant extends TestCase {

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/constants.php';
	}

	public function testShouldMockConstants() {
		Monkey\Functions\expect( 'rocket_has_constant' )
			->ordered()
			->once()
			->with( 'THIS_CONSTANT_DOES_NOT_EXIST' )
			->andReturn( true )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_PLUGIN_ROOT' )
			->andReturn( false );

		$this->assertTrue( rocket_has_constant( 'THIS_CONSTANT_DOES_NOT_EXIST' ) );
		// This constant is defined in the test suite's bootstrapping.
		$this->assertFalse( rocket_has_constant( 'WP_ROCKET_PLUGIN_ROOT' ) );
	}

	public function testShouldReturnFalseWhenConstantNotDefined() {
		$this->assertFalse( rocket_has_constant( 'THIS_CONSTANT_DOES_NOT_EXIST' ) );
	}

	public function testShouldReturnTrueWhenConstantIsDefined() {
		$this->assertTrue( rocket_has_constant( 'WP_ROCKET_PLUGIN_ROOT' ) );
		$this->assertTrue( rocket_has_constant( 'WP_ROCKET_TESTS_FIXTURES_DIR' ) );
	}
}
