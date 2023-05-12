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
		$this->controller = Mockery::mock(ClearCache::class . '[is_excluded,is_excluded_by_filter,is_private]', [$this->query])
			->shouldAllowMockingProtectedMethods();
	}


	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {

		foreach ($config['urls'] as $url) {
			$this->controller->expects()->is_private($url)->andReturn($config['is_private']);
			if ( ! $config['is_private'] ) {
				$this->controller->expects()->is_excluded_by_filter($url)->andReturn($config['is_excluded']);
				$this->controller->shouldReceive('is_excluded_by_filter')->with($url)->andReturn($config['is_excluded_by_filter']);
			}
			else{
				$this->controller->expects()->is_excluded_by_filter($url)->never();
				$this->controller->shouldReceive('is_excluded_by_filter')->never();
			}
		}

		if ( $config['is_private'] ) {
			$this->query->expects($this->never())->method('create_or_update');
			$this->query->expects($this->never())->method('delete_by_url');
		}
		else {
			if(! $config['is_excluded']) {
				if(! $config['is_excluded_by_filter']) {
					$this->query->expects(self::atLeastOnce())->method('create_or_update')->withConsecutive(...$expected['urls']);
				} else {
					$this->query->expects(self::atLeastOnce())->method('delete_by_url')->withConsecutive(...$expected['urls']);
				}
			}
		}

		$this->controller->partial_clean($config['urls']);
	}
}
