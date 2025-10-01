<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Controller;

use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\RocketInsights\Context\Context;
use WP_Rocket\Engine\Admin\RocketInsights\Controller;
use WP_Rocket\Engine\Admin\RocketInsights\Database\Queries\RocketInsights;
use WP_Rocket\Engine\Admin\RocketInsights\Jobs\Manager;
use WP_Rocket\Engine\Admin\RocketInsights\Credit\Manager as CreditManager;
use WP_Rocket\Engine\Admin\RocketInsights\GlobalScore;
use WP_Rocket\Engine\Admin\RocketInsights\Managers\Plan;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\RocketInsights\Controller::get_items
 *
 * @group Admin
 * @group SettingsPage
 */
class GetItemsTest extends TestCase {
	public function testShouldGetItems() {
		$mock_query = $this->createMock(RocketInsights::class);
		$expected_params = [
			'orderby' => 'modified',
			'order'   => 'asc',
			'number'  => 20,
		];
		$mock_query->expects($this->once())
			->method('query')
			->with($expected_params)
			->willReturn(['foo']);

		$mock_manager        = $this->createMock(Manager::class);
		$mock_context        = $this->createMock(Context::class);
		$mock_credit_manager = $this->createMock(CreditManager::class);
		$user = $this->createMock(User::class);
		$global_score = $this->createMock(GlobalScore::class);
		$options = $this->createMock(Options_Data::class);

		$controller = new Controller($mock_query, $mock_manager, $mock_context, $mock_credit_manager, $global_score, $user, $options);
		$result = $controller->get_items();

		$this->assertEquals(['foo'], $result);
	}
}
