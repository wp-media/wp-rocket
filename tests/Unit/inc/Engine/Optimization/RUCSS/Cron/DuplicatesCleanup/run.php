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
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$query       = $this->createMock( UsedCSS_Query::class );
		$table       = $this->createMock( UsedCSS_Table::class );
		$options_api = Mockery::mock( Options::class );
		$clock       = Mockery::mock( ClockInterface::class );
		$cleanup     = new DuplicatesCleanup( $query, $table, $options_api, $clock );

		$completes = 'done' === $expected && ! $config['done'];
		$runs      = ! $config['done'] && ! $config['locked'] && $config['index'];

		$options_api->shouldReceive( 'get' )->with( UsedCSS_Table::DEDUPED_OPTION, false )->andReturn( $config['done'] );

		Functions\expect( 'get_transient' )->times( $config['done'] ? 0 : 1 )->with( DuplicatesCleanup::LOCK )->andReturn( $config['locked'] );
		Functions\expect( 'set_transient' )->times( (int) $runs )->with( DuplicatesCleanup::LOCK, Mockery::type( 'int' ), 300 );
		Functions\expect( 'delete_transient' )->times( (int) $runs )->with( DuplicatesCleanup::LOCK );

		$table->expects( $this->exactly( $config['done'] || $config['locked'] ? 0 : 1 ) )
			->method( 'index_exists' )
			->with( UsedCSS_Table::UNIQUE_KEY )
			->willReturn( $config['index'] );

		$delete = $query->expects( $this->exactly( count( $config['calls'] ) ) )->method( 'delete_older_duplicates' );

		if ( ! empty( $config['calls'] ) ) {
			$delete->with( DuplicatesCleanup::BATCH )->willReturnOnConsecutiveCalls( ...$config['calls'] );
			$clock->shouldReceive( 'current_time' )->with( 'timestamp', true )->andReturn( ...$config['clock'] );
		}

		$options_api->shouldReceive( 'set' )->times( (int) $completes )->with( UsedCSS_Table::DEDUPED_OPTION, 1 );

		if ( $completes ) {
			Functions\when( 'wp_cache_supports' )->justReturn( $config['supports'] );
			Functions\expect( 'wp_cache_flush_group' )->times( (int) $config['supports'] )->with( 'wpr_used_css-by-job_id' );
		}

		$this->assertSame( $expected, $cleanup->run() );
	}
}
