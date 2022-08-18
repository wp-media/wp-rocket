<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Controller\LoadInitialSitemap;

use Mockery;
use WP_Rocket\Engine\Preload\Controller\CrawlHomepage;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Engine\Preload\Database\Queries\Cache;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Sitemaps;

/**
 * @covers \WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap::cancel_preload
 * @group  Preload
 */
class Test_CancelPreload extends TestCase
{
	protected $queue;
	protected $query;
	protected $controller;
	protected $crawler;

	protected function setUp(): void
	{
		parent::setUp();
		$this->queue = Mockery::mock(Queue::class);
		$this->query = $this->createMock(Cache::class);
		$this->crawler = Mockery::mock(CrawlHomepage::class);
		$this->controller = new LoadInitialSitemap($this->queue, $this->query, $this->crawler);
	}

	public function testShouldDoAsExpected() {
		$this->queue->expects()->cancel_pending_jobs();
		$this->query->expects(self::once())->method('revert_in_progress');
		$this->controller->cancel_preload();
	}
}
