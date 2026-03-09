<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\Admin\RocketInsights\Queue\Queue;
use WP_Rocket\Engine\Admin\RocketInsights\Subscriber;
use WP_Rocket\Engine\Admin\RocketInsights\Render;
use WP_Rocket\Engine\Admin\RocketInsights\Rest;
use WP_Rocket\Engine\Admin\RocketInsights\Controller;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Tests\Unit\TestCase;

class RenderPerformanceUrlsTableTest extends TestCase {
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
		$remaining_url_calls = 1;

		// Override with specific expectations if provided
		if (isset($config['call_count_expectations'])) {
			$remaining_url_calls = $config['call_count_expectations']['get_remaining_url_count'] ?? $remaining_url_calls;
		}

		$mock_controller->expects($this->exactly($remaining_url_calls))
			->method('get_remaining_url_count')
			->willReturn( $config['remaining_urls'] );

		$mock_controller->expects($this->exactly(1))
			->method('get_rocket_insights_addon_limit')
			->willReturn( $config['rocket_insights_addon_limit'] );

		$mock_controller->expects($this->once())
			->method('get_license_data')
			->willReturn( $config['license_data'] );

		$mock_render->expects($this->once())
			->method('render_rocket_insights_urls_table')
			->with($expected);

		$mock_rest = $this->createMock(Rest::class);
		$mock_queue = $this->createMock(Queue::class);
		$ri_context = $this->createMock(Context::class);
		$ri_context->expects($this->once())
			->method('is_allowed')
			->willReturn(true);
		$ri_context->expects($this->any())
			->method('is_adding_page_allowed')
			->willReturn(count( $config['items'] ) < $config['rocket_insights_addon_limit']);
		$options = $this->createMock(Options_Data::class);
		$manager = $this->createMock(Manager::class);

		$plan_mock = $this->createMock( Plan::class );
		$renewal_mock = $this->createMock( Renewal::class );

		$subscriber = new Subscriber($mock_render, $mock_controller, $mock_rest, $mock_queue, $ri_context, $mock_global_score, $options, $manager, $plan_mock, $renewal_mock);
		$subscriber->render_performance_urls_table();
	}
}
