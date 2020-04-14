<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\Combine;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use MatthiasMullie\Minify;
use Mockery;
use WP_Rocket\Engine\Optimization\Minify\CSS\Combine;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\Combine::optimize
 * @group Combine
 * @group CombineCSS
 */
class Test_Optimize extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/Combine/combine.php';
	private $combine;
	private $minify;

	public function setUp() {
		parent::setUp();

		$this->minify = Mockery::mock( Minify\CSS::class );
		$this->minify->shouldReceive( 'add' );
		$this->minify->shouldReceive( 'minify' )
			->andReturn( 'body{font-family:Helvetica,Arial,sans-serif;text-align:center;}' );

		Functions\expect( 'rocket_get_constant' )
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_PATH' )
			->andReturn( $this->filesystem->getUrl( 'wordpress/wp-content/cache/min/' ) )
			->andAlsoExpectIt()
			->once()
			->with( 'WP_ROCKET_MINIFY_CACHE_URL' )
			->andReturn( 'http://example.org/wp-content/cache/min/' );

		$this->combine = new Combine( $this->options, $this->minify );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldCombineCSS( $original, $expected, $cdn_host, $cdn_url, $site_url ) {
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

		$this->assertSame(
			$expected['html'],
			$this->combine->optimize( $original )
		);

		foreach ( $expected['files'] as $file ) {
			$this->assertTrue( $this->filesystem->exists( $file ) );
		}
	}
}
