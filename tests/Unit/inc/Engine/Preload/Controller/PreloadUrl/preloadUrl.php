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
		$this->controller = Mockery::mock(PreloadUrl::class . '[get_mobile_user_agent_prefix,is_already_cached,format_url,]',
			[$this->options, $this->queue, $this->query, $this->file_system])->shouldAllowMockingProtectedMethods();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );
		Filters\expectApplied('rocket_preload_query_string')->andReturn($config['query_activated']);
		Functions\expect('get_rocket_cache_query_string')->andReturn($config['cached_queries']);
		$this->isNoCached($config);
		$this->configureAlreadyCached($config);
		$this->configureRequest($config);
		$this->configureMobileRequest($config);
		$this->controller->preload_url($config['url']);
	}

	protected function isNoCached($config) {
		if($config['is_cached']) {
			return;
		}

		$this->query->expects(self::once())->method('delete_by_url')->with($config['url']);
	}

	protected function configureAlreadyCached($config) {

		if(! $config['is_cached']) {
			return;
		}
		$this->controller->expects()->format_url($config['url'], true)->andReturn($config['url']);
		$this->options->expects()->get('do_caching_mobile_files', false)->andReturn($config['cache_mobile']);
		$this->controller->expects()->is_already_cached($config['url'])->andReturn($config['cache_exists']);
	}

	protected function configureRequest($config) {
		if($config['cache_exists']) {
			return;
		}

		if(! $config['is_cached']) {
			return;
		}

		Functions\expect('wp_safe_remote_get')->with($config['url'], $config['request']['config']);
	}

	protected function configureMobileRequest($config) {
		if($config['cache_exists']) {
			return;
		}

		if(! $config['is_cached']) {
			return;
		}

		if(! $config['cache_mobile']) {
			return;
		}
		$this->controller->expects()->get_mobile_user_agent_prefix()->andReturn($config['user_agent']);
		Functions\expect('wp_safe_remote_get')->with($config['url'], $config['request_mobile']['config']);
	}
}
