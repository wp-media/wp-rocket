<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\ClearCache;

use WP_Rocket\Engine\Preload\Controller\ClearCache;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\ClearCache::full_clean
 * @group Preload
 */
class Test_FullClean extends TestCase
{
	protected $query;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createMock(Cache::class);
		$this->controller = new ClearCache($this->query);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {

		$this->query->expects(self::once())->method('query')->with([])->willReturn($config['urls']);

		$this->query->expects(self::atLeastOnce())->method('create_or_update')->withConsecutive(...$expected['urls']);

		$this->controller->full_clean();
	}
}
