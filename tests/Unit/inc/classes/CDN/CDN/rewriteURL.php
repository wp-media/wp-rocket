<?php

namespace WP_Rocket\Tests\Unit\inc\classes\CDN\CDN;

use Brain\Monkey\Functions;
use WP_Rocket\CDN\CDN;

/**
 * @covers \WP_Rocket\CDN\CDN::rewrite_url
 * @group  CDN
 */
class Test_RewriteURL extends TestCase {
	/**
	 * @dataProvider rewriteURLProvider
	 */
	public function testShouldReturnURLWithCDN( $url, $expected ) {
		Functions\when( 'get_option' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->alias( function( $url ) use ( $expected ) {
			$scheme = parse_url( $expected, PHP_URL_SCHEME );
			if ( ! $scheme ) {
				return 'http://' . $url;
			}

			return $scheme . '://' . $url;
		} );
		Functions\when( 'rocket_remove_url_protocol' )->alias( function( $url ) {
			return str_replace( [ 'http://', 'https://' ], '', $url );
		} );

		$cdn = new CDN( $this->getOptionsMock() );
		$this->assertSame( $expected, $cdn->rewrite_url( $url ) );
	}

	/**
	 * @dataProvider rewriteURLWithSchemeProvider
	 */
	public function testShouldReturnURLWithCDNWhenCDNURLContainsScheme( $url, $expected ) {
		Functions\when( 'get_option' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->returnArg();
		Functions\when( 'rocket_remove_url_protocol' )->alias( function( $url ) {
			return str_replace( [ 'http://', 'https://' ], '', $url );
		} );

		$map = [
			[ 'cdn', '', 1 ],
			[ 'cdn_cnames', [], [ 'https://cdn.example.org' ] ],
			[ 'cdn_reject_files', [], [] ],
			[ 'cdn_zone', [], [ 'all' ] ],
		];
		$cdn = new CDN( $this->getOptionsMock( $map ) );
		$this->assertSame( $expected, $cdn->rewrite_url( $url ) );
	}

	public function testShouldReturnURLWithCDNWhenZoneIsCSSJS() {
		Functions\when( 'get_option' )->justReturn( 'http://example.org' );
		Functions\when( 'rocket_add_url_protocol' )->returnArg();
		Functions\when( 'rocket_remove_url_protocol' )->alias( function( $url ) {
			return str_replace( [ 'http://', 'https://' ], '', $url );
		} );

		$map = [
			[ 'cdn', '', 1 ],
			[ 'cdn_cnames', [], [ 'https://cdn.example.org' ] ],
			[ 'cdn_reject_files', [], [ '/wp-content/uploads/file.jpg', '/wp-content/(.*).css' ] ],
			[ 'cdn_zone', [], [ 'css_and_js' ] ],
		];
		$cdn = new CDN( $this->getOptionsMock( $map ) );
		$this->assertSame(
			'https://cdn.example.org/style.css?ver=5.2.3',
			$cdn->rewrite_url( 'http://example.org/style.css?ver=5.2.3' )
		);

		$this->assertSame(
			'https://cdn.example.org/script.js',
			$cdn->rewrite_url( 'http://example.org/script.js' )
		);
	}

	/**
	 * @dataProvider excludedURLProvider
	 */
	public function testShouldReturnDefaultURL( $url ) {
		$cdn = new CDN( $this->getOptionsMock() );

		$this->assertSame( $url, $cdn->rewrite_url( $url ) );
	}

	public function testShouldReturnDefaultURLWhenRejectedFiles() {
		$map = [
			[ 'cdn', '', 1 ],
			[ 'cdn_cnames', [], [ 'cdn.example.org' ] ],
			[ 'cdn_reject_files', [], [ '/wp-content/uploads/file.jpg', '/wp-content/(.*).css' ] ],
			[ 'cdn_zone', [], [ 'images', 'css_and_js' ] ],
		];
		$cdn = new CDN( $this->getOptionsMock( $map ) );

		$this->assertSame(
			'http://example.org/wp-content/uploads/file.jpg',
			$cdn->rewrite_url( 'http://example.org/wp-content/uploads/file.jpg' )
		);

		$this->assertSame(
			'http://example.org/wp-content/themes/twentytwenty/style.css',
			$cdn->rewrite_url( 'http://example.org/wp-content/themes/twentytwenty/style.css' )
		);

		$this->assertSame(
			'http://example.org/wp-content/uploads/post.css',
			$cdn->rewrite_url( 'http://example.org/wp-content/uploads/post.css' )
		);
	}

	public function rewriteURLProvider() {
		return [
			[
				'http://example.org/wp-content/uploads/test.jpg',
				'http://cdn.example.org/wp-content/uploads/test.jpg',
			],
			[
				'http://example.org/wp-content/themes/twentynineteen/style.css',
				'http://cdn.example.org/wp-content/themes/twentynineteen/style.css',
			],
			[
				'/wp-includes/jquery.js',
				'http://cdn.example.org/wp-includes/jquery.js',
			],
			[
				'//example.org/wp-content/uploads/podcast.mp4',
				'//cdn.example.org/wp-content/uploads/podcast.mp4',
			],
		];
	}

	public function rewriteURLWithSchemeProvider() {
		return [
			[
				'http://example.org/wp-content/uploads/test.jpg',
				'https://cdn.example.org/wp-content/uploads/test.jpg',
			],
			[
				'http://example.org/wp-content/themes/twentynineteen/style.css',
				'https://cdn.example.org/wp-content/themes/twentynineteen/style.css',
			],
			[
				'/wp-includes/jquery.js',
				'https://cdn.example.org/wp-includes/jquery.js',
			],
			[
				'//example.org/wp-content/uploads/podcast.mp4',
				'//cdn.example.org/wp-content/uploads/podcast.mp4',
			],
		];
	}

	public function excludedURLProvider() {
		return [
			[
				'http://example.org/test.php',
			],
			[
				'http://example.org/',
			],
		];
	}
}
