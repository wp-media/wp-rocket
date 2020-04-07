<?php

namespace WP_Rocket\Tests\Integration\inc\admin;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers ::rocket_after_save_options
 * @group admin
 */
class Test_RocketAfterSaveOptions extends TestCase {
	private $option_name;
	private $options;

	public function setUp() {
		parent::setUp();

		$this->option_name = rocket_get_constant( 'WP_ROCKET_SLUG' );
		$this->options     = get_option( $this->option_name );
	}

	public function tearDown() {
		parent::tearDown();

		$this->silently_update_option( $this->options );
	}

	private function silently_update_option( $new_value ) {
		$hook_name     = "update_option_{$this->option_name}";
		$pre_hook_name = "pre_update_option_{$this->option_name}";

		// F "pre_update_option_{$option}"
		// F 'pre_update_option'
		// A 'update_option'
		// A "update_option_{$option}"
		// A 'updated_option'
		remove_action( $hook_name, 'rocket_after_save_options' );
		remove_filter( $pre_hook_name, 'rocket_pre_main_option' );
		update_option( $this->option_name, $new_value );
		add_action( $hook_name, 'rocket_after_save_options', 10, 2 );
		add_filter( $pre_hook_name, 'rocket_pre_main_option', 10, 2 );
	}

	public function testShouldNotTriggerCallbacksWhenInvalidValues() {
		Functions\expect( 'wp_json_encode' )->never();

		$this->silently_update_option( 'foo' );

		$this->assertTrue( 'foo' === get_option( $this->option_name ) );

		update_option( $this->option_name, 'bar' );

		$this->assertTrue( 'bar' === get_option( $this->option_name ) );

		update_option( $this->option_name, [] );

		$this->assertTrue( [] === get_option( $this->option_name ) );

		update_option( $this->option_name, 'bar' );

		$this->assertTrue( 'bar' === get_option( $this->option_name ) );
	}
}
