<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Activation;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Activation\Activation;
use WP_Rocket\Engine\Preload\Controller\LoadInitialSitemap;
use WP_Rocket\Engine\Preload\Controller\Queue;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Activation::activate
 * @group  Preload
 */
class Test_Activate extends TestCase
{

	protected $activation;
	protected $controller;
	protected $options;
	protected $queue;

	protected function setUp(): void
	{
		parent::setUp();
		$this->controller = Mockery::mock(LoadInitialSitemap::class);
		$this->options = Mockery::mock(Options_Data::class);
		$this->queue = Mockery::mock(Queue::class);
		$this->activation = new Activation($this->controller, $this->options, $this->queue);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected($config) {
		if($config['is_enabled']) {
			$this->controller->expects()->load_initial_sitemap();
		}
		$this->options->expects()->get('manual_preload', false)->andReturns($config['is_enabled']);
		$this->activation->activate();
	}
}
