<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Subscriber;

/**
 * @covers \WP_Rocket\Engine\CDN\Subscriber::get_cdn_hosts
 * @uses   \WP_Rocket\Engine\CDN\CDN::get_cdn_urls
 * @uses   ::rocket_add_url_protocol
 * @group  CDN
 */
class Test_GetCdnHosts extends TestCase {
	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnCdnArray( $original, $zones, $cdn_urls, $expected ) {
		$this->cnames = array_merge( $original, $cdn_urls );

		add_filter( 'rocket_cdn_cnames', [ $this, 'setCnames' ] );

		$this->assertSame(
			$expected,
			array_values( apply_filters( 'rocket_cdn_hosts', $original, $zones ) )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'get-cdn-hosts' );
	}
}
