<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDNSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Subscriber\CDN\CDNSubscriber;

class TestGetSubscribedEvents extends TestCase {
    public function testShouldReturnSubscribedEventsArray() {
        $events = [
			'rocket_buffer'           => [
                [ 'rewrite', 20 ],
                [ 'rewrite_srcset', 21 ],
            ],
			'rocket_css_content'      => 'rewrite_css_properties',
			'rocket_cdn_hosts'        => [ 'get_cdn_hosts', 10, 2 ],
			'rocket_dns_prefetch'     => 'add_dns_prefetch_cdn',
			'rocket_facebook_sdk_url' => 'add_cdn_url',
			'rocket_css_url'          => [ 'add_cdn_url', 10, 2 ],
			'rocket_js_url'           => [ 'add_cdn_url', 10, 2 ],
        ];

        $this->assertSame(
            $events,
            CDNSubscriber::get_subscribed_events()
        );
    }
}
