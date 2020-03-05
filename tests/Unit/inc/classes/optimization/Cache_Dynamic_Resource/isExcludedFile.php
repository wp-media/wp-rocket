<?php

namespace WP_Rocket\Tests\Unit\inc\optimization\Cache_Dynamic_Resource;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Optimization\Cache_Dynamic_Resource;

/**
 * @covers \WP_Rocket\Optimization\Cache_Dynamic_Resource::is_excluded_file
 * @group  Optimize
 * @group  CacheDynamicResource
 */
class Test_IsExcludedFile extends TestCase {
	private $cache_resource;

	public function setUp() {
		parent::setUp();

		Functions\when( 'get_current_blog_id' )->justReturn( 1 );

		$this->cache_resource = new Cache_Dynamic_Resource(
			$this->createMock( Options_Data::class ),
			'wp-content/cache/busting/',
			'http://example.org/wp-content/cache/busting/'
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

		Functions\when( 'content_url' )->justReturn( 'http://example.org/wp-content' );
		Functions\when( 'get_rocket_i18n_uri' )->justReturn( [
			'http://en.example.org',
			'https://example.de',
		] );
		Functions\when( 'rocket_extract_url_component' )->alias( function( $url, $component ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'rocket_clean_exclude_file' )->alias( function( $url ) {
			return parse_url( $url, PHP_URL_PATH );
		} );
		Functions\when( 'remove_query_arg' )->alias( function( $key, $query ) {
			return str_replace( [ '&ver=5.3', 'ver=5.3' ], '', $query );
		} );
	}

	/**
	 * @dataProvider excludedURLProvider
	 */
	public function testShouldReturnTrueWhenURLIsExcluded( $url ) {
		$this->assertTrue( $this->cache_resource->is_excluded_file( $url ) );
	}

	/**
	 * @dataProvider includedURLProvider
	 */
	public function testShouldReturnFalseWhenURLIsNotExcluded( $url ) {
		$this->assertFalse( $this->cache_resource->is_excluded_file( $url ) );
	}

	public function excludedURLProvider() {
		return [
			[ 'http://example.org/wp-content/themes/storefront/style.css' ],
			[ 'https://example.org/wp-content/themes/storefront/script.js' ],
			[ 'http://example.org' ],
			[ 'http://example.org/wp-admin/admin-ajax.php' ],
			[ 'https://example.org/wp-content/plugins/test/style.php?data=foo&ver=5.3' ],
			[ 'https://example.org/wp-content/plugins/test/script.php?data=foo' ],
			[ 'http://en.example.org/wp-content/plugins/test/style.css' ],
			[ 'https://example.de/wp-content/themes/storefront/assets/script.js?ver=5.3' ],
		];
	}

	public function includedURLProvider() {
		return [
			[ 'http://example.org/wp-content/themes/test/style.php' ],
			[ 'https://example.org/wp-content/themes/test/script.php?ver=5.3' ],
			[ 'http://example.org/wp-content/plugins/test/assets/custom.php' ],
			[ 'http://en.example.org/wp-content/plugins/test/style.php' ],
			[ 'https://example.de/wp-content/themes/storefront/assets/script.php?ver=5.3' ],
		];
	}
}
