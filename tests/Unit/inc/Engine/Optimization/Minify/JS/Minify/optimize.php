<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\JS\Minify;

use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\Minify\JS\Minify;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\JS\Minify::optimize
 *
 * @group  Optimize
 * @group  MinifyJS
 * @group  Minify
 */
class Test_Optimize extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/JS/Minify/optimize.php';
	protected $minify;
	private $local_cache;

	public function setUp() {
		parent::setUp();

		$GLOBALS['wp_scripts'] = (object) [
			'registered' => [
				'jquery-core' => (object) [
					'src' => 'wp-includes/js/jquery/jquery.js',
				],
			],
		];

		$this->options
			->shouldReceive( 'get' )
			->andReturnArg( 1 );

		$this->local_cache = Mockery::mock( AssetsLocalCache::class );
		$this->minify = new Minify( $this->options, $this->local_cache );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyJS( $original, $expected, $cdn_hosts, $cdn_url, $site_url, $external_url ) {
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

		$this->local_cache
			->shouldReceive( 'get_filepath' )
			->zeroOrMoreTimes()
			->with( $external_url )
			->andReturnUsing(function () use ($external_url) {
				$url_parts = parse_url($external_url);
				return'wp-content/cache/min/3rd-party/' .
					$url_parts['host'] . str_replace( '/', '-', $url_parts['path'] );
			});

		$this->local_cache
			->shouldReceive( 'get_content' )
			->zeroOrMoreTimes()
			->with( $external_url )
			->andReturn( 'console.log("hello world");' );

		$this->assertSame(
			$this->format_the_html( $expected['html'] ),
			$this->format_the_html( $this->minify->optimize( $original ) )
		);

		$this->assertFilesExists( $expected['files'] );
	}
}
