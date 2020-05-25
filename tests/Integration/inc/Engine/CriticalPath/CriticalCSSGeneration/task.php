<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CriticalPath\CriticalCSSGeneration;

use Mockery;
use WP_Error;
use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;

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
	protected static $processor;

	public function setUp() {
		parent::setUp();

		set_transient( 'rocket_critical_css_generation_process_running', [
			'generated' => 0,
			'total'     => 1,
			'items'     => [],
		] );

		self::$processor  = Mockery::mock( ProcessorService::class );
		self::$generation = new CriticalCSSGeneration( self::$processor );
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

		if ( false === $result['success'] ) {
			self::$processor->shouldReceive( 'process_generate' )
							->once()
							->andReturnUsing( function() use ( $result ) {
								return new WP_Error( $result['code'], $result['message'] );
							} );
		} else {
			self::$processor->shouldReceive( 'process_generate' )
							->once()
							->andReturn( $result );
		}

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
