<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Addons\VarnishSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Subscriber\Addons\Varnish\VarnishSubscriber;

class TestGetSubscribedEvents extends TestCase {
	public function testShouldReturnSubscribedEventsArray() {
        $events = [
			'before_rocket_clean_domain' => [ 'clean_domain', 10, 3 ],
			'before_rocket_clean_file'   => [ 'clean_file' ],
			'before_rocket_clean_home'   => [ 'clean_home', 10, 2 ],
        ];

        $this->assertSame(
            $events,
            VarnishSubscriber::get_subscribed_events()
        );
    }
}
