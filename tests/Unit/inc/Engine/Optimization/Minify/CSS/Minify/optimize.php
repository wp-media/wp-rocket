<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\Minify;

use Brain\Monkey\Functions;
use Brain\Monkey\Filters;
use Mockery;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\Minify\CSS\Minify;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\Minify::optimize
 *
 * @uses \WP_Rocket\Logger\Logger::info()
 * @uses \WP_Rocket\Logger\Logger::debug()
 * @uses \WP_Rocket\Logger\Logger::error()

 * @group  Optimize
 * @group  MinifyCSS
 * @group  Minify
 */
class Test_Optimize extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/Minify/optimize.php';
	protected $minify;
	private   $local_cache;

	public function setUp() {
		parent::setUp();

		$this->options
			->shouldReceive( 'get' )
			->andReturnArg( 1 );

		$this->local_cache = Mockery::mock( AssetsLocalCache::class );
		$this->minify = new Minify( $this->options, $this->local_cache );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyCSS( $original, $expected, $cdn_host, $cdn_url, $site_url, $external_url ) {
		Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'css' ] )
			->andReturn( $cdn_host );

		Filters\expectApplied( 'rocket_asset_url' )
			->zeroOrMoreTimes()
			->andReturnUsing( function( $url ) use ( $cdn_url, $site_url ) {
				return str_replace( $cdn_url, $site_url, $url );
			} );

		Filters\expectApplied( 'rocket_css_url' )
			->zeroOrMoreTimes()
			->andReturnUsing( function( $url, $original_url ) use ( $cdn_url ) {
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

		Functions\when( 'site_url' )->justReturn( $site_url );
		Functions\when( 'set_url_scheme')->alias( function ( $url ) {		 
			$url = trim( $url );
			if ( substr( $url, 0, 2 ) === '//' ) {
				$url = 'http:' . $url;
			}
		 
			return preg_replace( '#^\w+://#', 'http://', $url );
		});

		$this->local_cache
			->shouldReceive( 'get_content' )
			->zeroOrMoreTimes()
			->with( $external_url )
			->andReturn( 'external css content');

		$this->assertSame(
			$this->format_the_html( $expected['html'] ),
			$this->format_the_html( $this->minify->optimize( $original ) )
		);

		$this->assertFilesExists( $expected['files'] );
	}
}
