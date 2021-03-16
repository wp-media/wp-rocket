<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimiztion\RUCSS\Warmup\ResourceFetcherProcess;

use Mockery;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Queries\ResourcesQuery;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcherProcess;
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
	public function testShouldDoExpected( $resources ) {
		$resource = $resources[0];

		$resourcesQuery = Mockery::mock( ResourcesQuery::class );
//		$processor      = new ResourceFetcherProcess( $resourcesQuery );
//
//		$task = $this->get_reflective_method( 'task', ResourceFetcherProcess::class );
//
//		$resourcesQuery->shouldReceive( 'get_item_by' )
//			->once()
//			->with( 'url', $resource['url'] )
//			->andReturn( [] );
//
//		$resourcesQuery->shouldReceive( 'add_item' )
//			->once()
//			->with( [
//				'url'           => $resource['url'],
//				'type'          => $resource['type'],
//				'content'       => $resource['content'],
//				'hash'          => md5( $resource['content'] ),
//				'last_accessed' => gmdate( 'Y-m-d\TH:i:s\Z' ),
//			] );
//		$resourcesQuery->shouldReceive('update_item')
//			->once()
//			->with([
//				gmdate( 'Y-m-d\TH:i:s\Z' ),
//			]);

//		assertFalse( $task->invoke( $processor, $resources ) );
	}
}
