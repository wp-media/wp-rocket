<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\PreloadUrl;

use Mockery;
use Mockery\Mock;
use WP_Filesystem_Direct;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use Brain\Monkey\Filters;

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
	protected $file_system;

	protected function setUp(): void
	{
		parent::setUp();
		$this->options = Mockery::mock(Options_Data::class);
		$this->query = $this->createMock(Cache::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->file_system = Mockery::mock(WP_Filesystem_Direct::class);
		$this->controller = Mockery::mock(PreloadUrl::class . '[get_mobile_user_agent_prefix,is_already_cached]',
			[$this->options,
			$this->queue, $this->query, $this->file_system])->shouldAllowMockingProtectedMethods();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		$this->options->expects()->get('do_caching_mobile_files', false)->andReturn($config['cache_mobile']);
		$this->controller->expects()->is_already_cached($config['url'])->andReturn($config['cache_exists']);
		$this->configureRequest($config);
		$this->configureMobileRequest($config);
		$this->controller->preload_url($config['url']);
	}

	protected function configureRequest($config) {
		if($config['cache_exists']) {
			return;
		}

		Functions\expect('wp_safe_remote_get')->with($config['url'] . '/', $config['request']['config']);
	}

	protected function configureMobileRequest($config) {
		if($config['cache_exists']) {
			return;
		}

		if(! $config['cache_mobile']) {
			return;
		}
		$this->controller->expects()->get_mobile_user_agent_prefix()->andReturn($config['user_agent']);
		Functions\expect('wp_safe_remote_get')->with($config['url'] . '/', $config['request_mobile']['config']);
	}
}
