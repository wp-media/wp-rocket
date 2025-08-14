<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Render;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Controller;

class Test_RenderUI extends TestCase {
	public function testRenderUIFetchesAndRendersItems() {
		$mock_render = $this->createMock(Render::class);
		$mock_controller = $this->createMock(Controller::class);

		$mock_controller->expects($this->once())
			->method('get_items')
			->willReturn(['item1', 'item2']);

		$mock_render->expects($this->once())
			->method('render_ui')
			->with(['item1', 'item2']);

		$subscriber = new Subscriber($mock_render, $mock_controller);
		$subscriber->render_ui();
	}
}
