<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\RocketCache;

use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\RocketCache::remove_all_not_accessed_rows
 *
 * @group Database
 * @group Preload
 */
class Test_RemoveAllNotAccessedRows extends TestCase
{
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createPartialMock(RocketCache::class, ['query', 'delete_item']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDeleteAsExpected($config, $expected) {
		$this->query->expects(self::once())->method('query')->with($config['params'])->willReturn($config['results']);
		$this->query->expects(self::any())->method('delete_item')->withConsecutive(...$expected);
		$this->query->remove_all_not_accessed_rows();
	}
}
