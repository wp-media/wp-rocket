<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::get_cdn_hosts
 * @uses   \WP_Rocket\Engine\CDN\CDN::get_cdn_urls
 * @uses   ::rocket_add_url_protocol
 * @group  Subscriber
 * @group  CDN
 */
class Test_GetCdnHosts extends TestCase {
	public function addDataProvider() {
		return $this->getTestData( __DIR__, 'get-cdn-hosts' );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldReturnCdnArray( $original, $zones, $cdn_urls, $expected ) {
		$callback = function( $original ) use ( $cdn_urls ) {
			return array_merge( $original, $cdn_urls );
		};

		add_filter( 'rocket_cdn_cnames', $callback );

		$this->assertSame(
			$expected,
			array_values( apply_filters( 'rocket_cdn_hosts', $original, $zones ) )
		);

		remove_filter( 'rocket_cdn_cnames', $callback );
	}
}
