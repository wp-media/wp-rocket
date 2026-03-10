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

		$this->assertArrayHasKey( 'rocket_after_save_options', $events );
		$this->assertIsArray( $events['rocket_after_save_options'] );
		$this->assertSame( 'maybe_fetch_after_settings_change', $events['rocket_after_save_options'][0] );
		$this->assertSame( 10, $events['rocket_after_save_options'][1] );
		$this->assertSame( 2, $events['rocket_after_save_options'][2] );
	}
}
