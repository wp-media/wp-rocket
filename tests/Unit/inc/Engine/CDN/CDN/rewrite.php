<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\CDN;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\CDN\CDN;

/**
 * @covers \WP_Rocket\Engine\CDN\CDN::rewrite
 * @group  CDN
 */
class Test_Rewrite extends TestCase {

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldRewriteURLToCDN( $home_url, $original, $expected ) {
		Functions\when( 'content_url' )->justReturn( "{$home_url}/wp-content/" );
		Functions\when( 'includes_url' )->justReturn( "{$home_url}/wp-includes/" );
		Functions\when( 'wp_upload_dir' )->justReturn( "{$home_url}/wp-content/uploads/" );
		Functions\when( 'home_url' )->justReturn( $home_url );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) {
			if ( strpos( $url, 'http://' ) !== false || strpos( $url, 'https://' ) !== false ) {
				return $url;
			}

			if ( substr( $url, 0, 2 ) === '//' ) {
				return 'http:' . $url;
			}

			return 'http://' . $url;
		} );

		$cdn = new CDN( $this->getOptionsMock() );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $cdn->rewrite( $original ) )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'rewrite' );
	}
}
