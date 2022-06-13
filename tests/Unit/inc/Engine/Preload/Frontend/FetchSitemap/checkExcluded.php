<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Frontend\FetchSitemap;

use Mockery;
use WP_Rocket\Engine\Preload\Controller\PreloadUrl;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Engine\Preload\Frontend\FetchSitemap;
use WP_Rocket\Engine\Preload\Frontend\SitemapParser;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\FetchSitemap::check_excluded
 * @group  Preload
 */
class Test_CheckExcluded extends TestCase
{
	protected $sitemap_parser;
	protected $queue;
	protected $query;
	protected $controller;

	protected function setUp(): void
	{
		parent::setUp();
		$this->sitemap_parser = Mockery::mock(SitemapParser::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->query = $this->createMock(Cache::class);
		$this->controller = new FetchSitemap($this->sitemap_parser, $this->queue,
			$this->query);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnAsExpected($config, $expected) {
		Functions\expect('get_rocket_cache_reject_uri')->andReturn($config['excluded_urls']);
		$method = $this->get_reflective_method('check_excluded',  FetchSitemap::class);
		$this->assertSame($expected, $method->invokeArgs($this->controller,[$config['url']]));
	}
}
