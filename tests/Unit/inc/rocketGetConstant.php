<?php

namespace WP_Rocket\Tests\Unit\Inc;

use Brain\Monkey;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::rocket_has_constant
 * @uses  ::rocket_get_constant
 * @group Init
 * @group Constants
 */
class Test_RocketGetConstant extends TestCase {

	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/constants.php';
	}

	public function testShouldMockConstants() {
		Monkey\Functions\expect( 'rocket_get_constant' )
			->ordered()
			->once()
			->with( 'THIS_CONSTANT_DOES_NOT_EXIST' )
			->andReturn( 'Hello World' )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_PLUGIN_ROOT' )
			->andReturn( 'Hello World' );

		$this->assertSame( 'Hello World', rocket_get_constant( 'THIS_CONSTANT_DOES_NOT_EXIST' ) );
		$this->assertSame( 'Hello World', rocket_get_constant( 'WP_ROCKET_PLUGIN_ROOT' ) );
	}

	public function testShouldReturnDefaultWhenConstantNotDefined() {
		$this->assertNull( rocket_get_constant( 'THIS_CONSTANT_DOES_NOT_EXIST' ) );
		$this->assertSame( 'Hello World', rocket_get_constant( 'THIS_CONSTANT_DOES_NOT_EXIST', 'Hello World' ) );
	}

	public function testShouldReturnConstantWhenDefined() {
		$this->assertSame( WP_ROCKET_PLUGIN_ROOT, rocket_get_constant( 'WP_ROCKET_PLUGIN_ROOT' ) );
		$this->assertSame( WP_ROCKET_TESTS_FIXTURES_DIR, rocket_get_constant( 'WP_ROCKET_TESTS_FIXTURES_DIR' ) );
	}
}
