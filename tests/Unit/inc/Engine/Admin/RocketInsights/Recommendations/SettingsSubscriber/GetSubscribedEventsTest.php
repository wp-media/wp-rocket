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
		$this->assertIsArray( $events['update_option_wp_rocket_settings'] );
		$this->assertSame( 'maybe_fetch_after_settings_change', $events['update_option_wp_rocket_settings'][0] );
		$this->assertSame( 10, $events['update_option_wp_rocket_settings'][1] );
		$this->assertSame( 2, $events['update_option_wp_rocket_settings'][2] );
	}
}
