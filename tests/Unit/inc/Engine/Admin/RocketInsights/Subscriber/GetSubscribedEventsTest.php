<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Subscriber;

use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\Engine\Admin\RocketInsights\Subscriber;

class GetSubscribedEventsTest extends TestCase {
	public function testGetSubscribedEvents() {
		$this->assertArrayHasKey('rocket_insights_tab_content', Subscriber::get_subscribed_events());
	}
}
