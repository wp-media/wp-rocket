<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Render;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Controller;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\GlobalScore;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\AJAX\Controller as AjaxController;

class Test_RenderUI extends TestCase {
	public function testRenderUIFetchesAndRendersItems() {
		$mock_render = $this->createMock(Render::class);
		$mock_controller = $this->createMock(Controller::class);
		$mock_global_score = $this->createMock(GlobalScore::class);

		$mock_controller->expects($this->once())
			->method('get_items')
			->willReturn(['item1', 'item2']);

		$mock_render->expects($this->once())
			->method('render_ui')
			->with(['item1', 'item2']);

		$mock_ajax_controller = $this->createMock(AjaxController::class);

		$subscriber = new Subscriber($mock_render, $mock_controller, $mock_ajax_controller, $mock_global_score);
		$subscriber->render_ui();
	}
}
