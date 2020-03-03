<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\CDN\CDNSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\CDNSubscriber::get_cdn_hosts
 * @uses   \WP_Rocket\CDN\CDN::get_cdn_urls
 * @uses   ::rocket_add_url_protocol
 * @group  Subscriber
 * @group  CDN
 */
class Test_GetCdnHosts extends TestCase {
	public function tearDown() {
		delete_option( 'wp_rocket_settings' );

		parent::tearDown();
	}

	public function testShouldReturnOriginalArrayWhenNoCDNURL() {
		$this->assertSame(
			[],
			apply_filters( 'rocket_cdn_hosts', [], [ 'all' ] )
		);
	}

	public function testShouldReturnCDNHostsWhenExists() {
		$callback = function() {
			return [
				'http://cdn.example.org',
				'//cdn2.example.org',
				'https://cdn3.example.org',
				'cdn4.example.org',
			];
		};

		add_filter( 'rocket_cdn_cnames', $callback );

		$this->assertSame(
			[
				'cdn.example.org',
				'cdn2.example.org',
				'cdn3.example.org',
				'cdn4.example.org',
			],
			apply_filters( 'rocket_cdn_hosts', [], [ 'all' ] )
		);

		remove_filter( 'rocket_cdn_cnames', $callback );
	}

	public function testShouldReturnCDNHostsWhenExistsAndOriginalHasValue() {
		$callback = function( $hosts ) {
			return array_merge( $hosts, [
				'http://cdn.example.org',
				'//cdn2.example.org',
				'https://cdn3.example.org',
				'cdn4.example.org',
			] );
		};

		add_filter( 'rocket_cdn_cnames', $callback );

		$this->assertSame(
			[
				'cdn5.example.org',
				'cdn.example.org',
				'cdn2.example.org',
				'cdn3.example.org',
				'cdn4.example.org',
			],
			apply_filters( 'rocket_cdn_hosts', [ 'cdn5.example.org' ], [ 'all' ] )
		);

		remove_filter( 'rocket_cdn_cnames', $callback );
	}

	public function testShouldReturnCDNHostsWhenCDNHasPath() {
		$callback = function() {
			return [
				'http://cdn.example.org/path',
				'//cdn2.example.org'
			];
		};

		add_filter( 'rocket_cdn_cnames', $callback );

		$this->assertSame(
			[
				'cdn.example.org',
				'cdn2.example.org'
			],
			apply_filters( 'rocket_cdn_hosts', [], [ 'all' ] )
		);

		remove_filter( 'rocket_cdn_cnames', $callback );
	}

	public function testShouldReturnSpecificCDNHostsWhenSpecificZone() {
		$callback = function() {
			return [
				'http://cdn.example.org/path',
				'//cdn2.example.org'
			];
		};

		add_filter( 'rocket_cdn_cnames', $callback );

		$this->assertSame(
			[
				'cdn.example.org',
				'cdn2.example.org'
			],
			apply_filters( 'rocket_cdn_hosts', [], [ 'CSS', 'all' ] )
		);

		remove_filter( 'rocket_cdn_cnames', $callback );
	}
}
