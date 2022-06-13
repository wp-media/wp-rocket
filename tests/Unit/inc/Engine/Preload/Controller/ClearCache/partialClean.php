<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\ClearCache;

use Mockery;
use WP_Rocket\Engine\Preload\Controller\ClearCache;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\ClearCache::partial_clean
 * @group Preload
 */
class Test_PartialClean extends TestCase
{
	protected $query;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->query = $this->createMock(Cache::class);
		$this->controller = Mockery::mock(ClearCache::class . '[check_excluded]', [$this->query])->shouldAllowMockingProtectedMethods();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {

		foreach ($config['urls'] as $url) {
			$this->controller->expects()->check_excluded($url)->andReturn($config['is_excluded']);
		}
		if(! $config['is_excluded']) {
			$this->query->expects(self::atLeastOnce())->method('create_or_update')->withConsecutive(...$expected['urls']);
		}

		$this->controller->partial_clean($config['urls']);
	}
}
