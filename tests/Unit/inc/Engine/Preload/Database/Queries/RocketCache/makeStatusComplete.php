<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\RocketCache;

use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\RocketCache::make_status_complete
 * @uses \WP_Rocket\Engine\Preload\Database\Queries\RocketCacheRow
 *
 * @group Database
 * @group Preload
 */
class Test_MakeStatusComplete extends TestCase
{
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createPartialMock(RocketCache::class, ['get_rows_by_url', 'delete_item', 'query', 'update_item']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnPending($config, $expected) {
		$this->query->expects(self::once())->method('query')->with($config['query_params'])->willReturn($config['results']);
		$this->configureUpdate($config);
		$this->assertSame($expected, $this->query->make_status_complete($config['url']));
	}

	protected function configureUpdate($config) {
		if(! key_exists('task_id', $config)) {
			return;
		}

		$this->query->expects(self::once())->method('update_item')->with($config['task_id'], [
			'status' => 'completed'
		])->willReturn($config['update_status']);
	}
}
