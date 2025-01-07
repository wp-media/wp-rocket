<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\CriticalCSSGeneration;

use Brain\Monkey\Functions;
use Mockery;
use WP_Error;
use WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration;
use WP_Rocket\Engine\CriticalPath\ProcessorService;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CriticalPath\CriticalCSSGeneration::task
 *
 * @group  CriticalPath
 */
class test_Task extends TestCase {
	public static function setUpBeforeClass() : void {
		parent::setUpBeforeClass();

		require_once WP_ROCKET_TESTS_FIXTURES_DIR . '/WP_Error.php';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $item, $result, $transient ) {
		$processor  = Mockery::mock( ProcessorService::class );
		$generation = new CriticalCSSGeneration( $processor );

		$task = $this->get_reflective_method( 'task', CriticalCSSGeneration::class );

		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_critical_css_generation_process_running' )
			->andReturn( [
			'total'     => 1,
			'items'     => [],
		] );

		if ( false === $result['success'] ) {
			$processor->shouldReceive( 'process_generate' )
			->once()
			->andReturnUsing( function() use ( $result ) {
				return new WP_Error( $result['code'], $result['message'] );
			} );
		} else {
			$processor->shouldReceive( 'process_generate' )
			->once()
			->andReturn( $result );
		}

		Functions\when( 'is_wp_error' )->alias( function( $thing ) {
			return ( $thing instanceof WP_Error );
		} );

		if ( isset( $transient ) ) {
			Functions\expect( 'set_transient' )
				->once()
				->with(
					'rocket_critical_css_generation_process_running',
					$transient,
					HOUR_IN_SECONDS
				);
		} else {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_cpcss_generation_pending' )
				->andReturn( false );

			Functions\expect( 'set_transient' )
				->once()
				->with(
					'rocket_cpcss_generation_pending',
					[
						$item['path'] => $item,
					],
					HOUR_IN_SECONDS
				);
		}

		$this->assertFalse( $task->invoke( $generation, $item ) );
	}
}
