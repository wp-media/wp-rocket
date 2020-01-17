<?php

namespace WP_Rocket\Tests\Unit;

use Brain\Monkey;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use WP_Rocket\Tests\TestCaseTrait;

abstract class TestCase extends PHPUnitTestCase {
	use MockeryPHPUnitIntegration;
	use TestCaseTrait;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		// Rocket uses these constants for transients. It's safe to define them.
		if ( ! defined( 'MINUTE_IN_SECONDS' ) ) {
			define( 'MINUTE_IN_SECONDS', 60 );
			define( 'HOUR_IN_SECONDS', 60 * MINUTE_IN_SECONDS );
			define( 'DAY_IN_SECONDS', 24 * HOUR_IN_SECONDS );
			define( 'WEEK_IN_SECONDS', 7 * DAY_IN_SECONDS );
			define( 'MONTH_IN_SECONDS', 30 * DAY_IN_SECONDS );
			define( 'YEAR_IN_SECONDS', 365 * DAY_IN_SECONDS );
		}
	}

	/**
	 * Prepares the test environment before each test.
	 */
	protected function setUp() {
		parent::setUp();
		Monkey\setUp();
	}

	/**
	 * Cleans up the test environment after each test.
	 */
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
		Monkey\Functions\stubs(
			[
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
				'esc_textarea',
				'esc_url',
			]
		);

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
