<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Addons\CloudflareSubscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;

class TestGetSubscribedEvents extends TestCase {
	public function testShouldReturnSubscribedEventsArray() {
		$events = [
			'rocket_varnish_ip'                 => 'set_varnish_localhost',
			'rocket_varnish_purge_request_host' => 'set_varnish_purge_request_host',
		];

		$this->assertSame(
			$events,
			CloudflareSubscriber::get_subscribed_events()
		);
	}
}
