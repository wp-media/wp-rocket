<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\Minify\JS\Minify;

use Brain\Monkey\Filters;
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

		$this->minify = new Minify( $this->options );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldMinifyJS( $original, $expected, $cdn_hosts, $cdn_url, $site_url ) {
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
			$this->format_the_html( $this->minify->optimize( $original ) )
		);

		$this->assertFilesExists( $expected['files'] );
	}
}
