<?php

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
		$this->query = $this->createPartialMock(RocketCache::class, ['get_rows_by_url', 'query']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnPending($config, $expected) {
		$this->query->expects(self::atLeastOnce())->method('query')->withConsecutive([[
			'count'  => true,
			'status' => 'in-progress',
		]], [[
			'number'         => ( $config['total'] - $config['in_progress'] ),
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
		]])->willReturnOnConsecutiveCalls($config['in_progress'], $config['results']);
		$this->assertSame($expected, $this->query->get_pending_jobs($config['total']));
	}

}
