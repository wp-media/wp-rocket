<?php

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;

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
		$this->query->expects($this->at(0))->method('query')->with( [
			'count'  => true,
			'status' => 'in-progress',
		] )->willReturn( $config['in-progress'] );

		if ( $config['total'] > $config['in-progress'] ) {
			$this->query->expects($this->at(1))->method('query')->with([
				'number'         => $config['total'] - $config['in-progress'],
				'status'         => 'pending',
				'fields'         => [
					'id',
					'url',
				],
				'job_id__not_in' => [
					'not_in' => '',
				],
				'orderby'        => 'modified',
				'order'          => 'asc',
			])->willReturn($config['results']);
		}

		$this->assertSame($expected, $this->query->get_pending_jobs($config['total']));
	}

}
