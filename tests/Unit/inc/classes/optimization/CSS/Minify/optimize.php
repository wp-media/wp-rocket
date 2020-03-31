<?php
namespace WP_Rocket\Tests\Unit\inc\optimization\CSS\Minify;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use WP_Rocket\Optimization\CSS\Minify;
use WP_Rocket\Tests\Unit\inc\classes\optimization\TestCase;

/**
 * @covers \WP_Rocket\Optimization\CSS\Minify::optimize
 * @group Optimize
 * @group MinifyCSS
 */
class Test_Optimize extends TestCase {
	protected $path_to_test_data = '/inc/classes/optimization/CSS/Minify/optimize.php';
	protected $minify;

	public function setUp() {
		parent::setUp();

		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'wordpress/wp-content/cache/min/' ) )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_URL' )
			->andReturn( 'http://example.org/wp-content/cache/min/' );

		$this->minify = new Minify( $this->options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyCSS( $original, $minified, $cdn_host, $cdn_url, $site_url ) {
		Filters\expectApplied( 'rocket_cdn_hosts' )
			->zeroOrMoreTimes()
			->with( [], [ 'all', 'css_and_js', 'css' ] )
			->andReturn( $cdn_host );
		
		Filters\expectApplied( 'rocket_before_url_to_path' )
			->zeroOrMoreTimes()
			->andReturnUsing( function( $url ) use ( $cdn_url, $site_url ) {
				return str_replace( $cdn_url, $site_url, $url );
			} );

		Filters\expectApplied( 'rocket_css_url' )
			->zeroOrMoreTimes()
			->andReturnUsing( function( $url, $original_url ) use ( $cdn_url ) {
				return str_replace( 'http://example.org', $cdn_url, $url );
			} );

		$this->assertSame(
			$minified,
			$this->minify->optimize( $original )
		);
	}
}
