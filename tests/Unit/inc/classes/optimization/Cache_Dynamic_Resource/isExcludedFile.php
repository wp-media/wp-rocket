<?php

namespace WP_Rocket\Tests\Unit\inc\optimization\Cache_Dynamic_Resource;

use Brain\Monkey\Filters;
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
		Filters\expectApplied( 'rocket_cdn_hosts' )
			->atMost()
			->times(1)
			->with( [], [ 'all', 'css_and_js', '' ] )
			->andReturn( [
				'123456.rocketcdn.me',
				'cdn.example.org/path',
			]
		);

		Functions\when( 'get_rocket_i18n_uri' )->justReturn( [
			'http://en.example.org',
			'https://example.de',
		] );
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component ) {
			return parse_url( $url, $component );
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
		return $this->getTestData( __DIR__, 'excluded-urls' );
	}

	public function includedURLProvider() {
		return $this->getTestData( __DIR__, 'included-urls' );
	}
}
