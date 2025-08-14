<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\PerformanceMonitoring\Controller;

use Mockery;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Controller;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Database\Queries\PerformanceMonitoring;
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

		$controller = new Controller($mock_query);
		$result = $controller->get_items();

		$this->assertEquals(['foo'], $result);
	}
}
