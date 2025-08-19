<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\PerformanceMonitoring\Subscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\PerformanceMonitoring\Subscriber;

class Test_GetSubscribedEvents extends TestCase {
	public function testGetSubscribedEvents() {
		$this->assertArrayHasKey('rocket_dashboard_after_account_data', Subscriber::get_subscribed_events());
	}
}
