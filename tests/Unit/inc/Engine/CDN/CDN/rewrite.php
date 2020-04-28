<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CDN\CDN;

/**
 * @covers \WP_Rocket\Engine\CDN\CDN::rewrite
 * @group  CDN
 */
class Test_Rewrite extends TestCase {

	public function testShouldRewriteURLToCDN() {
		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content/' );
		Functions\when( 'includes_url' )->justReturn( 'http://example.org/wp-includes/' );
		Functions\when( 'wp_upload_dir' )->justReturn( 'http://example.org/wp-content/uploads/' );
		Functions\when( 'site_url' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			return 'http://' . $url;
		} );

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.html' );
		$rewrite  = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/rewrite.html' );

		$cdn = new CDN( $this->getOptionsMock() );
		$this->assertSame( $rewrite, $cdn->rewrite( $original ) );
	}

	public function testShouldRewriteURLToCDNWhenHomeContainsSubdir() {
		Functions\when( 'content_url' )->justReturn( 'https://example.org/blog/wp-content/' );
		Functions\when( 'includes_url' )->justReturn( 'https://example.org/blog/wp-includes/' );
		Functions\when( 'wp_upload_dir' )->justReturn( 'https://example.org/blog/wp-content/uploads/' );
		Functions\when( 'site_url' )->justReturn( 'https://example.org/blog' );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			return 'https://' . $url;
		} );

		$original = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/subdir/original.html' );
		$rewrite  = file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/subdir/rewrite.html' );

		$cdn = new CDN( $this->getOptionsMock() );
		$this->assertSame( $rewrite, $cdn->rewrite( $original ) );
	}
}
