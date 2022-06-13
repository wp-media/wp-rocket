<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\PreloadUrl;

use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\PreloadUrl::check_excluded
 * @group  Preload
 */
class Test_CheckExcluded extends TestCase
{
	protected $queue;
	protected $query;
	protected $file_system;
	protected $options;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->query = $this->createMock(Cache::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->file_system = Mockery::mock(WP_Filesystem_Direct::class);
		$this->controller = new PreloadUrl($this->options, $this->queue, $this->query, $this->file_system);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\expect('get_rocket_cache_reject_uri')->andReturn($config['excluded_urls']);
		$method = $this->get_reflective_method('check_excluded',  PreloadUrl::class);
		$this->assertSame($expected, $method->invokeArgs($this->controller,[$config['url']]));
	}
}
