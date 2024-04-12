<?php

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\RocketCache::get_pending_jobs
 *
 * @group Database
 * @group Preload
 */
class Test_GetPendingJobs extends TestCase {
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createPartialMock(Cache::class, ['get_rows_by_url', 'query']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnPending($config, $expected) {
		$queue = new SplQueue();

		if($config['total'] > 0 && $config['in-progress'] < $config['total'] ) {
			$queue->enqueue(
				[
					'params' =>
						[
							'count'     => true,
							'status'    => 'in-progress',
							'is_locked' => false,
						],
					'return' => $config['in-progress']
				]
			);

			$queue->enqueue(
				[
					'params' =>
						[
							'number'         => $config['total']-$config['in-progress'],
							'status'         => 'pending',
							'fields'         => [
								'id',
								'url',
							],
							'job_id__not_in' => [
								'not_in' => '',
							],
							'orderby'        => Filters\applied( 'rocket_preload_order' ) > 0 ? 'id' : 'modified',
							'order'          => 'asc',
							'is_locked' => false
						],
					'return' => $config['results']
				]
			);

			$this->query->expects(self::exactly(2))->method('query')
				->willReturnCallback(function($params) use ($queue) {
					$dequeue = $queue->dequeue();
					$this->assertEquals($dequeue['params'], $params);
					return $dequeue['return'];
				});
		} else {
			$this->query->expects(self::once())->method('query')->with([
				'count' => true,
				'status' => 'in-progress',
				'is_locked' => false,
			])->willReturn($config['in-progress']);

		}

		$this->assertSame($expected, $this->query->get_pending_jobs($config['total']));
	}

}
