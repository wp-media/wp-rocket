<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;

use Brain\Monkey\Functions;
use SplQueue;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Common\Database\Queries\AbstractQuery::get_pending_jobs
 *
 * @group RUCSS
 */
class Test_GetPendingJobs extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected( $config, $expected ) {
		Functions\when( 'current_time' )->justReturn( $config['now'] );

		$usedcss = $this->createPartialMock( UsedCSS::class, [ 'query' ] );
		$queue   = new SplQueue();

		foreach ( $expected['queries'] as $query ) {
			$queue->enqueue( $query );
		}

		/* @phpstan-ignore-next-line */
		$usedcss->expects( self::exactly( count( $expected['queries'] ) ) )
			->method( 'query' )
			->willReturnCallback(
				function ( $args, $use_cache = true ) use ( $queue ) {
					$next = $queue->dequeue();

					$this->assertSame( $next['args'], $args );
					$this->assertSame( $next['use_cache'], $use_cache );

					return $next['return'];
				}
			);

		$result = isset( $config['count'] )
			? $usedcss->get_pending_jobs( $config['count'] )
			: $usedcss->get_pending_jobs();

		$this->assertSame( $expected['result'], $result );
	}
}
