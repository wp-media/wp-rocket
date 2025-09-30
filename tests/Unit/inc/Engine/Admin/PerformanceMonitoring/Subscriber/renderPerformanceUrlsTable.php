<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs\Manager;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Managers\Plan;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Queue\Queue;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Render;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Controller;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\GlobalScore;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\AJAX\Controller as AjaxController;

class Test_renderPerformanceUrlsTable extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testFetchesAndRendersItems($config, $expected) {
		$mock_render = $this->createMock(Render::class);
		$mock_controller = $this->createMock(Controller::class);
		$mock_global_score = $this->createMock(GlobalScore::class);

		$mock_controller->expects($this->once())
			->method('get_items')
			->willReturn( $config['items'] );

		$mock_controller->expects($this->once())
			->method('get_global_score')
			->willReturn( $config['score'] );

		// Determine call counts based on user type
		$remaining_url_calls = $config['is_free'] ? 2 : 1; // Free users call twice (main array + quota banner)

		// Override with specific expectations if provided
		if (isset($config['call_count_expectations'])) {
			$remaining_url_calls = $config['call_count_expectations']['get_remaining_url_count'] ?? $remaining_url_calls;
		}

		$mock_controller->expects($this->exactly($remaining_url_calls))
			->method('get_remaining_url_count')
			->willReturn( $config['remaining_urls'] );

		$mock_controller->expects($this->exactly(1))
			->method('get_pma_addon_limit')
			->willReturn( $config['pma_addon_limit'] );

		$mock_controller->expects($this->once())
			->method('get_license_data')
			->willReturn( $config['license_data'] );

		// Mock current credit for quota banner logic (only for free users)
		if ($config['is_free'] && isset($config['has_credits'])) {
			$mock_controller->expects($this->once())
				->method('has_credit')
				->willReturn( $config['has_credits'] ? 1 : 0 );
		}

		$mock_render->expects($this->once())
			->method('render_pma_urls_table')
			->with($expected);

		$mock_ajax_controller = $this->createMock(AjaxController::class);
		$mock_queue = $this->createMock(Queue::class);
		$pm_context = $this->createMock(PerformanceMonitoringContext::class);
		$pm_context->expects($this->once())
			->method('is_allowed')
			->willReturn(true);
		$pm_context->expects($this->exactly(2))
			->method('is_free_user')
			->willReturn($config['is_free']);
		$pm_context->expects($this->any())
			->method('is_adding_page_allowed')
			->willReturn(count( $config['items'] ) < $config['pma_addon_limit']);
		$options = $this->createMock(Options_Data::class);
		$manager = $this->createMock(Manager::class);

		$plan_mock = $this->createMock( Plan::class );

		$subscriber = new Subscriber($mock_render, $mock_controller, $mock_ajax_controller, $mock_queue, $pm_context, $mock_global_score, $options, $manager, $plan_mock);
		$subscriber->render_performance_urls_table();
	}
}
