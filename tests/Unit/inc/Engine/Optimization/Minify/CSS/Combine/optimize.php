<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\CSS\Combine;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use MatthiasMullie\Minify;
use Mockery;
use WP_Rocket\Engine\Optimization\AssetsLocalCache;
use WP_Rocket\Engine\Optimization\Minify\CSS\Combine;
use WP_Rocket\Tests\Unit\inc\Engine\Optimization\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\Minify\CSS\Combine::optimize
 *
 * @group  Combine
 * @group  CombineCSS
 * @group  Optimize
 * @group  MinifyCSS
 * @group  Minify
 */
class Test_Optimize extends TestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/Minify/CSS/Combine/combine.php';
	private   $combine;
	private   $local_cache;

	public function setUp() {
		parent::setUp();

		$this->options
			 ->shouldReceive( 'get' )
			 ->andReturnArg( 1 );

		$this->local_cache = Mockery::mock( AssetsLocalCache::class );
		$this->combine     = new Combine( $this->options, $this->local_cache );
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
			->andReturnUsing( function ( $url ) use ( $cdn_url, $site_url ) {
				return str_replace( $cdn_url, $site_url, $url );
			} );

		Filters\expectApplied( 'rocket_css_url' )
			->zeroOrMoreTimes()
			->andReturnUsing( function ( $url, $original_url ) use ( $cdn_url ) {
				return str_replace( 'http://example.org', $cdn_url, $url );
			} );

		$this->local_cache->shouldReceive( 'get_content' )
			->zeroOrMoreTimes()
			->with( 'https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css' )
			->andReturn( "@font-face{font-family:'FontAwesome';src:url('../fonts/fontawesome-webfont.eot?v=4.7.0');src:url('../fonts/fontawesome-webfont.eot?#iefix&v=4.7.0') format('embedded-opentype'),url('../fonts/fontawesome-webfont.woff2?v=4.7.0') format('woff2'),url('../fonts/fontawesome-webfont.woff?v=4.7.0') format('woff'),url('../fonts/fontawesome-webfont.ttf?v=4.7.0') format('truetype'),url('../fonts/fontawesome-webfont.svg?v=4.7.0#fontawesomeregular') format('svg');font-weight:normal;font-style:normal}" );

		Functions\when( 'esc_url' )->returnArg();

		$this->assertSame(
			$expected['html'],
			$this->combine->optimize( $original )
		);

		$this->assertFilesExists( $expected['files'] );
	}
}
