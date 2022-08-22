<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\Cache::remove_all_not_accessed_rows
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
		$this->query = $this->createPartialMock(Cache::class, ['query', 'delete_item', 'get_old_cache']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDeleteAsExpected($config, $expected) {
		$this->query->expects(self::once())->method('get_old_cache')->willReturn($config['results']);
		$this->query->expects(self::any())->method('delete_item')->withConsecutive(...$expected);
		$this->query->remove_all_not_accessed_rows();
	}
}
