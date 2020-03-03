<?php

namespace WP_Rocket\Tests\Unit\inc\classes\subscriber\CDN\CDNSubscriber;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\CDN\CDN;
use WP_Rocket\Subscriber\CDN\CDNSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\CDNSubscriber::get_cdn_hosts
 * @group  CDN
 */
class Test_GetCdnHosts extends TestCase {
	private $cdn;
	private $subscriber;

	public function setUp() {
		parent::setUp();

		$this->cdn        = $this->createMock( CDN::class );
		$this->subscriber = new CDNSubscriber(
			$this->createMock( Options_Data::class ),
			$this->cdn
		);

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			if ( strpos( $url, 'http://' ) !== false || strpos( $url, 'https://' ) !== false ) {
				return $url;
			}

			if ( substr( $url, 0, 2 ) === '//' ) {
				return 'http:' . $url;
			}

			return 'http://' . $url;
		} );
	}

	public function testShouldReturnOriginalArrayWhenNoCDNURL() {
		$this->cdn->expects( $this->once() )
			->method( 'get_cdn_urls' )
			->willReturn( [] );

		$this->assertSame(
			[],
			$this->subscriber->get_cdn_hosts( [], [ 'all' ] )
		);
	}

	public function testShouldReturnCDNHostsWhenExists() {
		$this->cdn->expects( $this->once() )
			->method( 'get_cdn_urls' )
			->willReturn( [
				'http://cdn.example.org',
				'//cdn2.example.org',
				'https://cdn3.example.org',
				'cdn4.example.org',
			] );

		$this->assertSame(
			[
				'cdn.example.org',
				'cdn2.example.org',
				'cdn3.example.org',
				'cdn4.example.org',
			],
			$this->subscriber->get_cdn_hosts( [], [ 'all' ] )
		);
	}

	public function testShouldReturnCDNHostsWhenExistsAndOriginalHasValue() {
		$this->cdn->expects( $this->once() )
			->method( 'get_cdn_urls' )
			->willReturn( [
				'http://cdn.example.org',
				'//cdn2.example.org',
				'https://cdn3.example.org',
				'cdn4.example.org',
			] );

		$this->assertSame(
			[
				'cdn5.example.org',
				'cdn.example.org',
				'cdn2.example.org',
				'cdn3.example.org',
				'cdn4.example.org',
			],
			$this->subscriber->get_cdn_hosts( [ 'cdn5.example.org' ], [ 'all' ] )
		);
	}

	public function testShouldReturnCDNHostsWhenCDNHasPath() {
		$this->cdn->expects( $this->once() )
			->method( 'get_cdn_urls' )
			->willReturn( [
				'http://cdn.example.org/path',
				'//cdn2.example.org'
			] );

		$this->assertSame(
			[
				'cdn.example.org',
				'cdn2.example.org'
			],
			$this->subscriber->get_cdn_hosts( [], [ 'all' ] )
		);
	}
}
