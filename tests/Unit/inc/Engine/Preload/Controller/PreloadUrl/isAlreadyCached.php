<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\PreloadUrl;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Filesystem_Direct;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\PreloadUrl::is_already_cached
 * @group  Preload
 */
class Test_IsAlreadyCached extends TestCase
{
	protected $queue;
	protected $query;
	protected $options;
	protected $controller;
	protected $file_system;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->query = $this->createMock(Cache::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->file_system = Mockery::mock(WP_Filesystem_Direct::class);
		$this->controller = Mockery::mock(PreloadUrl::class . '[get_mobile_user_agent_prefix]', [$this->options, $this->queue, $this->query, $this->file_system])->shouldAllowMockingProtectedMethods();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config, $expected) {
		Functions\when('is_ssl')->justReturn($config['ssl']);
		Functions\when('get_rocket_parse_url')->justReturn($config['parsed_url']);
		Functions\when('path')->justReturn($config['parsed_url']['path']);
		Functions\when('rocket_get_constant')->alias(function ($name) {
			return $name;
		});

		if($config['ssl']) {
			$this->options->shouldReceive('get')->with('cache_ssl')->zeroOrMoreTimes()->andReturn($config['cache_ssl']);
		}

		if($config['cache_webp']) {
			$this->file_system->shouldReceive('exists')->with($config['nowebp_path'])->zeroOrMoreTimes()->andReturn($config['nowebp_path_exists']);
			$this->file_system->expects()->exists($config['webp_path'])->andReturn($config['webp_path_exists']);
			$this->file_system->shouldReceive('exists')->with($config['cache_path'])->zeroOrMoreTimes()->andReturn($config['cache_path_exists']);

		} else {
			$this->file_system->expects()->exists($config['cache_path'])->andReturn($config['cache_path_exists']);
		}

		$this->options->expects()->get('cache_webp', false)->andReturn($config['cache_webp']);

		$this->assertSame($expected, $this->controller->is_already_cached($config['url']));
	}
}
