<?php
namespace WP_Rocket\Tests\Unit;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Brain\Monkey;

class TestCase extends PHPUnitTestCase {
	protected function setUp() {
		parent::setUp();
		Monkey\setUp();
	}

	protected function tearDown() {
		Monkey\tearDown();
		parent::tearDown();
	}

	/**
	 * Mock common WP functions.
	 *
	 * @since  3.4
	 * @author Grégory Viguier
	 * @access protected
	 */
	protected function mockCommonWpFunctions() {
		$functions = [
			'__',
			'esc_attr__',
			'esc_html__',
			'_x',
			'esc_attr_x',
			'esc_html_x',
			'_n',
			'_nx',
			'esc_attr',
			'esc_html',
		];

		foreach ( $functions as $function ) {
			Monkey\Functions\when( $function )->returnArg();
		}

		$functions = [
			'_e',
			'esc_attr_e',
			'esc_html_e',
			'_ex',
		];

		foreach ( $functions as $function ) {
			Monkey\Functions\when( $function )->echoArg();
		}
	}
}
