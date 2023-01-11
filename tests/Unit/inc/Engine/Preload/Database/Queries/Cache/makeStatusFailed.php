<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class Test_MakeStatusFailed extends TestCase
{
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createPartialMock(Cache::class, ['update_item', 'query']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\expect('current_time')->andReturn($config['current_time']);
		$this->query->expects(self::once())->method('update_item')->with($config['task_id'], [
			'status' => 'failed',
			'modified' => $config['current_time'],
		])->willReturn($config['update_status']);
		$this->assertSame($expected, $this->query->make_status_failed($config['task_id']));
	}
}
