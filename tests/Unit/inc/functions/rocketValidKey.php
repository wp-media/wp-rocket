<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_valid_key
 *
 * @group Functions
 * @group Options
 */
class Test_RocketValidKey extends TestCase {
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $options, $set_transient, $expected ) {
		Functions\when( 'get_rocket_option' )->alias(
			function ( $option, $default = '' ) use ( $options ) {
				return array_key_exists( $option, $options ) ? $options[ $option ] : $default;
			}
		);

		// rocket_valid_key() can run before the textdomain loads, so it must never translate.
		Functions\expect( '__' )->never();
		Functions\expect( '_n' )->never();

		if ( false === $set_transient ) {
			Functions\expect( 'set_transient' )->never();
		} else {
			Functions\expect( 'set_transient' )
				->once()
				->with( 'rocket_check_key_errors', $set_transient )
				->andReturn( true );
		}

		$this->assertSame( $expected, rocket_valid_key() );
	}
}
