<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Subscriber;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::get_cdn_hosts
 * @group  CDN
 */
class Test_GetCdnHosts extends TestCase {
	private $cdn;
	private $subscriber;

	public function setUp() : void {
		parent::setUp();

		$this->cdn        = Mockery::mock( CDN::class );
		$this->subscriber = new Subscriber(
			Mockery::mock( Options_Data::class ),
			$this->cdn
		);

		Functions\when( 'get_rocket_parse_url' )->alias( function( $url ) {
			$parsed = parse_url( $url );

			$host     = isset( $parsed['host'] ) ? strtolower( urldecode( $parsed['host'] ) ) : '';
			$path     = isset( $parsed['path'] ) ? urldecode( $parsed['path'] ) : '';
			$scheme   = isset( $parsed['scheme'] ) ? urldecode( $parsed['scheme'] ) : '';
			$query    = isset( $parsed['query'] ) ? urldecode( $parsed['query'] ) : '';
			$fragment = isset( $parsed['fragment'] ) ? urldecode( $parsed['fragment'] ) : '';

			return [
				'host'     => $host,
				'path'     => $path,
				'scheme'   => $scheme,
				'query'    => $query,
				'fragment' => $fragment,
			];
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

	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'get-cdn-hosts' );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldReturnCdnArray( $original, $zones, $cdn_urls, $expected ) {
		$this->cdn->shouldReceive( 'get_cdn_urls' )
			->once()
			->andReturn( $cdn_urls );

		$this->assertSame(
			$expected,
			array_values( $this->subscriber->get_cdn_hosts( $original, $zones ) )
		);
	}
}
