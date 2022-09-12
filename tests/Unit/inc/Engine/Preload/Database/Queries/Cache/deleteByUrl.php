<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\Cache::delete_by_url
 *
 * @group Database
 * @group Preload
 */
class Test_DeleteByUrl extends TestCase
{
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createPartialMock(Cache::class, ['get_rows_by_url', 'delete_item']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->query->expects(self::once())->method('get_rows_by_url')->with($config['url'])->willReturn($config['results']);
		$this->configureDelete($config);
		$this->assertSame($expected, $this->query->delete_by_url($config['url']));
	}

	protected function configureDelete($config) {
		if(count($config['results']) === 0) {
			return;
		}
		$this->query->expects(self::exactly(2))->method('delete_item')->withConsecutive([$config['delete_id_one']], [$config['delete_id_two']])->willReturnOnConsecutiveCalls($config['delete_return_one'], $config['delete_return_two']);
	}
}
