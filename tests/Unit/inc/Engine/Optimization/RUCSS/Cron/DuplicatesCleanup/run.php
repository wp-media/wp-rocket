<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Cron\DuplicatesCleanup;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Common\Clock\ClockInterface;
use WP_Rocket\Engine\Optimization\RUCSS\Cron\DuplicatesCleanup;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS as UsedCSS_Query;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS as UsedCSS_Table;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Cron\DuplicatesCleanup::run
 *
 * @group RUCSS
 */
class Test_Run extends TestCase {
	private $query;
	private $options_api;
	private $clock;
	private $cleanup;

	protected function setUp(): void {
		parent::setUp();

		$this->query       = $this->createMock( UsedCSS_Query::class );
		$this->options_api = Mockery::mock( Options::class );
		$this->clock       = Mockery::mock( ClockInterface::class );
		$this->cleanup     = new DuplicatesCleanup( $this->query, $this->options_api, $this->clock );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->options_api->shouldReceive( 'get' )->with( UsedCSS_Table::DEDUPED_OPTION, false )->andReturn( $config['done'] );

		if ( $config['done'] ) {
			$this->expectDeleteCalls( [] );
			Functions\expect( 'get_transient' )->never();

			$this->assertSame( $expected['result'], $this->cleanup->run() );
			return;
		}

		Functions\expect( 'get_transient' )->once()->with( DuplicatesCleanup::LOCK )->andReturn( $config['locked'] );

		if ( $config['locked'] ) {
			$this->expectDeleteCalls( [] );
			Functions\expect( 'set_transient' )->never();

			$this->assertSame( $expected['result'], $this->cleanup->run() );
			return;
		}

		Functions\expect( 'set_transient' )->once()->with( DuplicatesCleanup::LOCK, Mockery::type( 'int' ), 300 );
		Functions\expect( 'delete_transient' )->once()->with( DuplicatesCleanup::LOCK );

		$this->options_api->shouldReceive( 'get' )->with( DuplicatesCleanup::PROGRESS_OPTION, [] )->andReturn( $config['progress'] );

		if ( empty( $config['progress'] ) ) {
			$this->query->expects( $this->once() )->method( 'get_max_id' )->willReturn( $config['max_id'] );
			Functions\expect( 'add_option' )
				->once()
				->with( 'wp_rocket_' . DuplicatesCleanup::PROGRESS_OPTION, [ 'cursor' => 0, 'max' => $config['max_id'] ], '', false );
		} else {
			$this->query->expects( $this->never() )->method( 'get_max_id' );
		}

		$this->clock->shouldReceive( 'current_time' )->with( 'timestamp', true )->andReturn( ...$config['clock'] );

		$this->expectDeleteCalls( $config['calls'] );

		foreach ( $expected['saves'] as $save ) {
			$this->options_api->shouldReceive( 'set' )->once()->with( DuplicatesCleanup::PROGRESS_OPTION, $save );
		}

		if ( 'done' === $expected['result'] ) {
			$this->options_api->shouldReceive( 'set' )->once()->with( UsedCSS_Table::DEDUPED_OPTION, 1 );
			$this->options_api->shouldReceive( 'delete' )->once()->with( DuplicatesCleanup::PROGRESS_OPTION );

			Functions\when( 'wp_cache_supports' )->justReturn( $config['supports'] );

			if ( $config['supports'] ) {
				Functions\expect( 'wp_cache_flush_group' )->once()->with( 'wpr_used_css-by-job_id' );
			} else {
				Functions\expect( 'wp_cache_flush_group' )->never();
			}
		} else {
			$this->options_api->shouldReceive( 'set' )->with( UsedCSS_Table::DEDUPED_OPTION, 1 )->never();
		}

		$this->assertSame( $expected['result'], $this->cleanup->run() );
	}

	/**
	 * Expects the given delete_older_duplicates() calls, as [ after_id, up_to_id, limit, result ] lists.
	 *
	 * @param array $calls Expected calls.
	 *
	 * @return void
	 */
	private function expectDeleteCalls( array $calls ) {
		$method = $this->query->expects( $this->exactly( count( $calls ) ) )->method( 'delete_older_duplicates' );

		if ( empty( $calls ) ) {
			return;
		}

		$method->withConsecutive( ...array_map( function ( $call ) {
			return array_slice( $call, 0, 3 );
		}, $calls ) )->willReturnOnConsecutiveCalls( ...array_column( $calls, 3 ) );
	}
}
