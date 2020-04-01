<?php

namespace WP_Rocket\Tests\Integration\inc\classes\subscriber\CDN\CDNSubscriber;

/**
 * @covers \WP_Rocket\Subscriber\CDN\CDNSubscriber::maybe_replace_url
 * @uses   \WP_Rocket\CDN\CDN::get_cdn_urls
 * @group  Subscriber
 * @group  CDN
 */
class Test_MaybeReplaceUrl extends TestCase {
	public function addDataProvider() {
        return $this->getTestData( __DIR__, 'maybe-replace-url' );
	}

	/**
	 * @dataProvider addDataProvider
	 */
	public function testShouldMaybeReplaceURL( $original, $zones, $cdn_urls, $site_url, $expected ) {
		$callback_cnames = function( $original ) use ( $cdn_urls ) {
			return array_merge( $original, $cdn_urls );
		};

		$callback_site_url = function() use ( $site_url ) {
			return $site_url;
		};

		add_filter( 'rocket_cdn_cnames', $callback_cnames );
		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true'] );
		add_filter( 'site_url', $callback_site_url );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_asset_url', $original, $zones )
		);

		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_true'] );
		remove_filter( 'rocket_cdn_cnames', $callback_cnames );
		remove_filter( 'site_url', $callback_site_url );
	}
}
