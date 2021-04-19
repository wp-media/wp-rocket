<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Warmup\ResourceFetcher;

use Mockery;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcher;
use WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcherProcess;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Warmup\ResourceFetcher::handle
 *
 * @group  RUCSS
 */
class Test_Handle extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/RUCSS/Warmup/ResourceFetcher/Handle.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldDoExpected( $input, $expected ){

		$local_cache = Mockery::mock( AssetsLocalCache::class );
		$process = Mockery::mock( ResourceFetcherProcess::class );

		$resource_fetcher = new ResourceFetcher( $local_cache, $process );

		Functions\when( 'wp_unslash' )->alias(
			function ( $value ) {
				return stripslashes( $value );
			}
		);

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );

		$site_url = $config['site_url'] ?? 'http://example.org/';
		$home_url = $config['home_url'] ?? 'http://example.org/';

		Functions\when( 'site_url' )->alias( function( $path = '') use ( $site_url ) {
			return $site_url . ltrim( $path, '/' );
		} );

		Functions\when( 'home_url' )->alias( function( $path = '') use ( $home_url ) {
			return $home_url . ltrim( $path, '/' );
		} );

		Functions\when( 'wp_basename' )->alias( function ( $path, $suffix = '' ) {
			return urldecode( basename( str_replace( array( '%2F', '%5C' ), '/', urlencode( $path ) ), $suffix ) );
		} );

		Functions\when( 'content_url' )->justReturn( "{$home_url}/wp-content/" );

		Functions\when( 'wp_make_link_relative' )->alias( function( $url ) {
			return preg_replace( '|^(https?:)?//[^/]+(/?.*)|i', '$2', $url );
		} );

		Functions\when( 'sanitize_text_field' )->returnArg();

		if ( empty( $expected['resources'] ) ) {
			$process
				->shouldReceive( 'push_to_queue' )
				->never();
		}else{

			foreach ( $expected['resources'] as $resource ) {
				$process
					->shouldReceive( 'push_to_queue' )
					->with( $resource )
					->once();
			}

			$process
				->shouldReceive( 'save' )
				->once()
				->andReturnSelf();

			$process
				->shouldReceive( 'dispatch' )
				->once()
				->andReturn( null );
		}

		$_POST = [
			'html' => $input['html'],
		];

		Functions\when( 'set_url_scheme')->alias( function ( $url ) {
			$url = trim( $url );
			if ( substr( $url, 0, 2 ) === '//' ) {
				$url = 'http:' . $url;
			}

			return preg_replace( '#^\w+://#', 'http://', $url );
		});

		$resource_fetcher->handle();

	}
}
