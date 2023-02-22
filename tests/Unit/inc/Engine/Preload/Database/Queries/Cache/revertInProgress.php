<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\Cache::revert_in_progress
 *
 * @group Database
 * @group Preload
 */
class Test_RevertInProgress extends TestCase
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
	public function testShouldDoAsExpected($config, $expected) {
		Functions\expect('current_time')->andReturn($config['current_time']);
		$this->query->expects(self::once())->method('query')->with([
			'status' => 'in-progress',
		])->willReturn($config['results']);
		$this->query->expects(self::atLeast(0))->method('update_item')->withConsecutive(...$expected);
		$this->query->revert_in_progress();
	}
}
