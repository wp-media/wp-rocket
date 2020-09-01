<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\JS\Combine;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Dependencies\Minify;
use Mockery;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\Minify\JS\Combine;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\JS\Combine::optimize
 *
 * @group  Optimize
 * @group  CombineJS
 * @group  MinifyJS
 * @group  Minify
 */
class Test_Optimize extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/JS/Combine/combine.php';
	private   $combine;
	private   $minify;

	public function setUp() {
		parent::setUp();

		$this->minify = Mockery::mock( Minify\JS::class );
		$this->minify->shouldReceive( 'add' );
		$this->minify->shouldReceive( 'minify' )
		             ->andReturn( 'minified JS' );

		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'wp_scripts' )->alias( function () {
			$wp_scripts                                 = new \stdClass();
			$jquery                                     = new \stdClass();
			$jquery->src                                = '/wp-includes/js/jquery/jquery.js';
			$wp_scripts->registered                = [];
			$wp_scripts->registered['jquery-core'] = $jquery;
			$wp_scripts->queue                     = [];

			return $wp_scripts;
		} );

		Functions\when( 'site_url' )->alias( function( $path = '') {
			return 'http://example.org/' . ltrim( $path, '/' );
		} );
		Functions\when( 'rocket_clean_exclude_file' )->alias( function( $file = '' ) {
			return parse_url( $file, PHP_URL_PATH );
		} );

		$this->options->shouldReceive( 'get' )
			->with( 'minify_js_key', 'rocket_uniqid' )
			->andReturn( 'rocket_uniqid' );
		$this->options->shouldReceive( 'get' )
			->with( 'exclude_inline_js', [] )
			->andReturn( [] );
		$this->options->shouldReceive( 'get' )
			->with( 'defer_all_js', 0 )
			->andReturn( 1 );
		$this->options->shouldReceive( 'get' )
			->with( 'defer_all_js_safe', 0 )
			->andReturn( 1 );
		$this->options->shouldReceive( 'get' )
			->with( 'exclude_js', [] )
			->andReturn( [] );

		$this->combine = new Combine( $this->options, $this->minify, Mockery::mock( AssetsLocalCache::class ) );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCombineJS( $original, $expected, $cdn_hosts, $cdn_url, $site_url ) {
		Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'js' ] )
			->andReturn( $cdn_hosts );

		Filters\expectApplied( 'rocket_asset_url' )
			->zeroOrMoreTimes()
			->andReturnUsing( function ( $url ) use ( $cdn_url, $site_url ) {
				return str_replace( $cdn_url, $site_url, $url );
			} );

		Filters\expectApplied( 'rocket_js_url' )
			->zeroOrMoreTimes()
			->andReturnUsing( function ( $url, $original_url ) use ( $cdn_url ) {
				return str_replace( 'http://example.org', $cdn_url, $url );
			} );

		$this->assertSame(
			$this->format_the_html( $expected['html'] ),
			$this->format_the_html( $this->combine->optimize( $original ) )
		);

		$this->assertFilesExists( $expected['files'] );
	}
}
