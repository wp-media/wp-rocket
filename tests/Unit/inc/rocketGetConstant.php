<?php

namespace WP_Rocket\Tests\Unit\Inc;

use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey;

/**
 * @group Init
 * @group Constants
 * @covers ::rocket_get_constant
 */
class Test_RocketGetConstant extends TestCase {
	protected function setUp() {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/constants.php';
	}

	/**
	 * Test rocket_get_constant() should mock getting constants, allowing tests to override what gets returned.
	 */
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
