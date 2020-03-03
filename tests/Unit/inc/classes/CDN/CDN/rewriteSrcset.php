<?php

namespace WP_Rocket\Tests\Unit\inc\classes\CDN\CDN;

use WP_Rocket\CDN\CDN;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\CDN\CDN::rewrite_srcset
 * @group  CDN
 */
class Test_RewriteSrcset extends TestCase {

	public function testShouldRewriteURLToCDN() {
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content/' );
		Functions\when( 'includes_url' )->justReturn( 'http://example.org/wp-includes/' );
		Functions\when( 'wp_upload_dir' )->justReturn( 'http://example.org/wp-content/uploads/' );
		Functions\when( 'get_option' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			return 'http://' . $url;
		} );

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.html' );
		$rewrite  = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/srcset/rewrite.html' );

		$cdn = new CDN( $this->getOptionsMock() );
		$this->assertSame( $rewrite, $cdn->rewrite_srcset( $original ) );
	}

	public function testShouldRewriteURLToCDNWhenZoneIsImages() {
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content/' );
		Functions\when( 'includes_url' )->justReturn( 'http://example.org/wp-includes/' );
		Functions\when( 'wp_upload_dir' )->justReturn( 'http://example.org/wp-content/uploads/' );
		Functions\when( 'get_option' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			return 'http://' . $url;
		} );

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.html' );
		$rewrite  = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/srcset/rewrite.html' );

		$map = [
			[ 'cdn', '', 1 ],
			[ 'cdn_cnames', [], [ 'cdn.example.org' ] ],
			[ 'cdn_reject_files', [], [] ],
			[ 'cdn_zone', [], [ 'images' ] ],
		];
		$cdn = new CDN( $this->getOptionsMock( $map ) );
		$this->assertSame( $rewrite, $cdn->rewrite_srcset( $original ) );
	}
}
