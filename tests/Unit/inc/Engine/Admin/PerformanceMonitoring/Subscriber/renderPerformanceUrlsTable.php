<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue\Queue;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Render;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Controller;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\GlobalScore;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\AJAX\Controller as AjaxController;

class Test_renderPerformanceUrlsTable extends TestCase {
	public function testFetchesAndRendersItems() {
		$mock_render = $this->createMock(Render::class);
		$mock_controller = $this->createMock(Controller::class);
		$mock_global_score = $this->createMock(GlobalScore::class);

		$items = ['item1', 'item2'];
		$score = [
			'status'    => 'in-progress',
			'pages_num' => 1,
			'score'     => 85,
		];

		$mock_controller->expects($this->once())
			->method('get_items')
			->willReturn( $items );

		$mock_controller->expects($this->once())
			->method('get_global_score')
			->willReturn( $score );

		$mock_render->expects($this->once())
			->method('render_pma_urls_table')
			->with([
				'items'        => $items,
				'global_score' => $score,
			]);

		$mock_ajax_controller   = $this->createMock(AjaxController::class);
		$mock_queue             = $this->createMock(Queue::class);
		$pm_context = $this->createMock(PerformanceMonitoringContext::class);

		$subscriber = new Subscriber($mock_render, $mock_controller, $mock_ajax_controller, $mock_queue, $pm_context, $mock_global_score);
		$subscriber->render_performance_urls_table();
	}
}
