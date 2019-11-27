<?php
namespace WP_Rocket\Tests\Unit;

use PHPUnit\Framework\TestCase as PHPUnitTestCase;
use Brain\Monkey;

class TestCase extends PHPUnitTestCase {
	use \Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

	/**
     * @var \Faker\Generator
     */
    protected $faker;
    
    /**
     * @var \Brain\Faker\Providers
     */
	protected $wpFaker;

	protected function setUp() {
		parent::setUp();
		Monkey\setUp();

		$this->faker = \Brain\faker();
        $this->wpFaker = $this->faker->wp();
	}

	protected function tearDown() {
		\Brain\fakerReset();

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
