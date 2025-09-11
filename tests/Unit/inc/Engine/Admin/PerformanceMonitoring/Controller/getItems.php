<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\PerformanceMonitoring\Controller;

use WP_Rocket\Engine\Admin\PerformanceMonitoring\Context\PerformanceMonitoringContext;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Controller;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Jobs\Manager;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\GlobalScore;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\PerformanceMonitoring\Controller::get_items
 *
 * @group Admin
 * @group SettingsPage
 */
class Test_GetItems extends TestCase {
	public function testShouldGetItems() {
		$mock_query = $this->createMock(PerformanceMonitoring::class);
		$expected_params = [
			'orderby' => 'modified',
			'order'   => 'asc',
			'number'  => 20,
		];
		$mock_query->expects($this->once())
			->method('query')
			->with($expected_params)
			->willReturn(['foo']);

		$mock_manager = $this->createMock(Manager::class);
		$mock_context = $this->createMock(PerformanceMonitoringContext::class);
		$global_score = $this->createMock(GlobalScore::class);
		$controller = new Controller($mock_query, $mock_manager, $mock_context, $global_score);
		$result = $controller->get_items();

		$this->assertEquals(['foo'], $result);
	}
}
