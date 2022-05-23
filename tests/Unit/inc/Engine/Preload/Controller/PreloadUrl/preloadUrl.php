<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\PreloadUrl;

use Mockery\Mock;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\RocketCache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\PreloadUrl::preload_url
 * @group  Preload
 */
class Test_PreloadUrl extends TestCase
{
	protected $queue;
	protected $query;
	protected $options;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = \Mockery::mock(Options_Data::class);
		$this->query = $this->createMock(RocketCache::class);
		$this->queue = \Mockery::mock(Queue::class);
		$this->controller = \Mockery::mock(PreloadUrl::class . '[get_mobile_user_agent_prefix]', [$this->options, $this->queue, $this->query]);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		$this->configureRequest($config);
		$this->options->expects()->get('cache_mobile', false)->andReturn($config['cache_mobile']);
		$this->configureMobileRequest($config);
		$this->query->expects(self::once())->method('make_status_complete')->with($config['url']);
		$this->controller->preload_url($config['url']);
	}



	protected function configureRequest($config) {
		Functions\expect('wp_remote_get')->with($config['url'], $config['request']['config']);
	}

	protected function configureMobileRequest($config) {
		if(! $config['cache_mobile']) {
			return;
		}
		$this->controller->expects()->get_mobile_user_agent_prefix()->andReturn($config['user_agent']);
		Functions\expect('wp_remote_get')->with($config['url'], $config['request_mobile']['config']);
	}
}
