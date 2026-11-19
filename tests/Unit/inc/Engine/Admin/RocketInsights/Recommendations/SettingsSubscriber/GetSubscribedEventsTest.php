<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;

use WP_Rocket\Engine\Admin\RocketInsights\Recommendations\SettingsSubscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class for SettingsSubscriber::get_subscribed_events()
 */
class GetSubscribedEventsTest extends TestCase {
	public function testGetSubscribedEvents() {
		$events = SettingsSubscriber::get_subscribed_events();

		$this->assertArrayHasKey( 'update_option_wp_rocket_settings', $events );
		$callbacks = $events['update_option_wp_rocket_settings'];

		$this->assertSame( [ 'maybe_clear_on_rocketcdn_paid', 9, 2 ], $callbacks[0] );
		$this->assertSame( [ 'maybe_fetch_after_settings_change', 10, 2 ], $callbacks[1] );
	}
}
