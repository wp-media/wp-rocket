<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimiztion\RUCSS\Warmup\WarmupProcess;

use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\APIClient;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\WarmupProcess;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcherProcess::task
 *
 * @group  RUCSS
 */
class test_Task extends TestCase {

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $resourceId, $returnedItem, $sentSuccess, $expected ) {

		$resourcesQuery = Mockery::mock( ResourcesQuery::class );
		$APIClient = Mockery::mock( APIClient::class );
		$process      = new WarmupProcess( $resourcesQuery, $APIClient );

		$task = $this->get_reflective_method( 'task', ResourceFetcherProcess::class );

		if ( is_int($resourceId) ) {
			$resourcesQuery->shouldReceive( 'get_item' )
				->once()
				->with( $resourceId )
				->andReturn( $returnedItem );
		}

		if ( $returnedItem ) {
			$APIClient->shouldReceive('send_warmup_request')
				->once()
				->with([
					'url' => $returnedItem['url'],
					'type' => $returnedItem['type'],
					'content' => $returnedItem['content'],
				])
			->andReturn($sentSuccess);
		}

		if ( $sentSuccess ) {
			$resourcesQuery->shouldReceive( 'item_update' )
				->once()
				->with($returnedItem['id']);
		}

		if ( $expected && $sentSuccess ) {
			assertTrue( $task->invoke( $process, $resourceId ) );
		} else {
			assertFalse( $task->invoke( $process, $resourceId ) );
		}
	}
}
