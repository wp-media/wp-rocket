<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\CDN\CDNSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\CDNSubscriber::get_cdn_hosts
 * @uses   \WP_Rocket\CDN\CDN::get_cdn_urls
 * @uses   ::rocket_add_url_protocol
 * @group  Subscriber
 */
class Test_GetCdnHosts extends TestCase {

	public function testShouldReturnCDNHosts() {
		update_option(
			'wp_rocket_settings',
			[
				'cdn'              => '1',
				'cdn_cnames'       => [ 'cdn.example.org' ],
				'cdn_zone'         => [ 'all' ],
				'cdn_reject_files' => [],
			]
		);
		$hosts = [ 'example.org' ];

		$this->assertSame(
			[ 'example.org', 'cdn.example.org' ],
			$this->getSubscriberInstance()->get_cdn_hosts( $hosts, 'all' )
		);
	}
}
