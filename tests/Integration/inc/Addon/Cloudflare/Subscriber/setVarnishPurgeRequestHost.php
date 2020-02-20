<?php

namespace WP_Rocket\Tests\Integration\inc\Addons\Cloudflare\Subscriber;

/**
 * @covers WPMedia\Cloudflare\Subscriber::set_varnish_purge_request_host
 * @group  DoCloudflare
 * @group  Addons
 */
class Test_SetVarnishPurgeRequestHost extends TestCase {

	public function testShouldReturnDefaultWhenCloudflareDisabled() {
		$this->setOptions( [ 'do_cloudflare' => 0 ] );

		$this->assertSame(
			'example.org',
			apply_filters( 'rocket_varnish_purge_request_host', 'example.org' )
		);
	}

	public function testShouldReturnDefaultWhenVarnishDisabled() {
		$this->setOptions(
			[
				'do_cloudflare'      => 1,
				'varnish_auto_purge' => 0,
			]
		);

		$this->assertSame(
			'example.org',
			apply_filters( 'rocket_varnish_purge_request_host', 'example.org' )
		);
	}

	public function testShouldReturnCurrentHostWhenVarnishEnabled() {
		$this->setOptions(
			[
				'do_cloudflare'      => 1,
				'varnish_auto_purge' => 1,
			]
		);

		$this->assertSame(
			'example.org',
			apply_filters( 'rocket_varnish_purge_request_host', 'test.local' )
		);
	}

	public function testShouldReturnCurrentHostWhenFilterTrue() {
		$this->setOptions(
			[
				'do_cloudflare'      => 1,
				'varnish_auto_purge' => 0,
			]
		);

		add_filter( 'do_rocket_varnish_http_purge', '__return_true' );

		$this->assertSame(
			'example.org',
			apply_filters( 'rocket_varnish_purge_request_host', 'test.local' )
		);

		remove_filter( 'do_rocket_varnish_http_purge', '__return_true' );
	}
}
