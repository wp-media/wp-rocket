<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\PreloadUrl;

use Mockery;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Filters;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\PreloadUrl::get_mobile_user_agent_prefix
 * @group  Preload
 */
class Test_getMobileUserAgentPrefix extends TestCase
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
	public function testShouldDoAsExpected($config, $expected) {
		Filters\expectApplied('rocket_mobile_preload_user_agent_prefix')->with($config['prefix'])->andReturn($config['filter']);
		$method = $this->get_reflective_method('get_mobile_user_agent_prefix',  PreloadUrl::class);
		$this->assertSame($expected, $method->invokeArgs($this->controller,[]));
	}
}
