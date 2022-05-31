<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\Cache::get_rows_by_url
 *
 * @group Database
 * @group Preload
 */
class Test_GetRowsByUrl extends TestCase
{
	protected $query;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createPartialMock(Cache::class, ['query']);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		$this->query->expects(self::once())->method('query')->with(['url' => $config['url']])->willReturn($config['result']);
		$this->assertSame($expected, $this->query->get_rows_by_url($config['url']));
	}
}
