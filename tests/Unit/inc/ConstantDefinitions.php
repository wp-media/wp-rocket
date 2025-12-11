<?php

namespace WP_Rocket\Tests\Unit\inc;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering constant definitions with pre-existing values
 *
 * @group init
 */
class Test_ConstantDefinitions extends TestCase {

	/**
	 * Test that constants can be safely defined when not already set.
	 */
	public function testShouldDefineConstantsWhenNotSet() {
		// Mock sanitize_key function
		Functions\when( 'sanitize_key' )->alias( function ( $key ) {
			return strtolower( str_replace( ' ', '-', $key ) );
		} );

		// Create a test constant name to avoid conflicts
		$test_name_const = 'TEST_ROCKET_NAME_' . uniqid();
		$test_slug_const = 'TEST_ROCKET_SLUG_' . uniqid();

		// Simulate the logic from main.php
		if ( ! defined( $test_name_const ) ) {
			define( $test_name_const, 'WP Rocket' );
		}
		if ( ! defined( $test_slug_const ) ) {
			define( $test_slug_const, sanitize_key( constant( $test_name_const ) ) );
		}

		// Verify constants are defined correctly
		$this->assertTrue( defined( $test_name_const ) );
		$this->assertTrue( defined( $test_slug_const ) );
		$this->assertSame( 'WP Rocket', constant( $test_name_const ) );
		$this->assertSame( 'wp-rocket', constant( $test_slug_const ) );
	}

	/**
	 * Test that pre-defined constants are not overwritten.
	 */
	public function testShouldNotOverwritePreDefinedConstants() {
		// Mock sanitize_key function
		Functions\when( 'sanitize_key' )->alias( function ( $key ) {
			return strtolower( str_replace( ' ', '-', $key ) );
		} );

		// Create test constants with custom values (simulating wp-config.php)
		$test_name_const = 'TEST_ROCKET_NAME_PREDEFINED_' . uniqid();
		$test_slug_const = 'TEST_ROCKET_SLUG_PREDEFINED_' . uniqid();
		
		define( $test_name_const, 'Site Cache' );
		define( $test_slug_const, 'site-cache' );

		// Simulate the logic from main.php - should NOT redefine
		if ( ! defined( $test_name_const ) ) {
			define( $test_name_const, 'WP Rocket' );
		}
		if ( ! defined( $test_slug_const ) ) {
			define( $test_slug_const, sanitize_key( constant( $test_name_const ) ) );
		}

		// Verify constants retain their pre-defined values
		$this->assertSame( 'Site Cache', constant( $test_name_const ) );
		$this->assertSame( 'site-cache', constant( $test_slug_const ) );
	}
}
