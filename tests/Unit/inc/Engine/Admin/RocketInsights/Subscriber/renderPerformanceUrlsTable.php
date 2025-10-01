<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\Admin\RocketInsights\Queue\Queue;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\RocketInsights\Subscriber;
use WP_Rocket\Engine\Admin\RocketInsights\Render;
use WP_Rocket\Engine\Admin\RocketInsights\Controller;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\AJAX\Controller as AjaxController;

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

		$mock_controller->expects($this->once())
			->method('get_rocket_insights_addon_limit')
			->willReturn( 1 );

		$mock_controller->expects($this->once())
			->method('get_remaining_url_count')
			->willReturn( 0 );

		$mock_controller->expects($this->once())
			->method('get_license_data')
			->willReturn( [] );

		$mock_render->expects($this->once())
			->method('render_rocket_insights_urls_table')
			->with([
				'items'        => $items,
				'global_score' => $score,
				'remaining_urls' => 0,
				'rocket_insights_addon_limit' => 1,
				'upgrade_url'    => '',
				'can_add_pages'  => true,
				'is_free' => false,
			]);

		$mock_ajax_controller   = $this->createMock(AjaxController::class);
		$mock_queue             = $this->createMock(Queue::class);
		$pm_context = $this->createMock(Context::class);
		$pm_context->expects($this->once())
			->method('is_allowed')
			->willReturn(true);
		$options = $this->createMock(Options_Data::class);
		$manager = $this->createMock(Manager::class);

		$plan_mock = $this->createMock( Plan::class );

		$subscriber = new Subscriber($mock_render, $mock_controller, $mock_ajax_controller, $mock_queue, $pm_context, $mock_global_score, $options, $manager, $plan_mock);
		$subscriber->render_performance_urls_table();
	}
}
