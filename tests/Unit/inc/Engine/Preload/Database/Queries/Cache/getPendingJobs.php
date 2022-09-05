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
		$this->query->expects(self::atLeastOnce())->method('query')->with([
			'number'         => $config['total'],
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
		$this->assertSame($expected, $this->query->get_pending_jobs($config['total']));
	}

}
