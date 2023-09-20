<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Database\Queries\Cache;

use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Database\Queries\Cache::is_preloaded
 *
 * @group Database
 * @group Preload
 */
class Test_IsPreloaded extends TestCase
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
		$this->query->expects(self::once())->method('query')->with([
			'url' => $config['url'],
			'count'  => true,
			'status' => 'in-progress',
			])->willReturn($config['result']);
		$this->assertSame($expected, $this->query->is_preloaded($config['url']));
	}
}
