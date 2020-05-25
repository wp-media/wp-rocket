<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSGeneration;

use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration::task
 *
 * @group CriticalPath
 * @group task
 */
class test_Task extends TestCase {
	protected static $transients = [
		'rocket_critical_css_generation_process_running' => null,
	];
	protected static $generation;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();

		$container        = apply_filters( 'rocket_container', null );
		self::$generation = $container->get( 'critical_css_generation' );
	}

	public function setUp() {
		parent::setUp();

		set_transient( 'rocket_critical_css_generation_process_running', [
			'generated' => 0,
			'total'     => 1,
			'items'     => [],
		] );
	}

	public function tearDown() {
		delete_transient( 'rocket_critical_css_generation_process_running' );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $item, $result, $transient, $expected ) {
		$task = $this->get_reflective_method( 'task', CriticalCSSGeneration::class );

		if ( false === $expected ) {
			$this->assertFalse( $task->invoke( self::$generation, $item ) );
			$this->assertSame(
				$transient,
				get_transient( 'rocket_critical_css_generation_process_running' )
			);
		} else {
			$this->assertSame(
				$expected,
				$task->invoke( self::$generation, $item )
			);
			$this->assertSame(
				self::$transients['rocket_critical_css_generation_process_running'],
				$transient
			);
		}
	}
}
