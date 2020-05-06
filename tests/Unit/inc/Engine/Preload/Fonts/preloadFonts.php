<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Fonts;

use Brain\Monkey\Functions;
use Mockery;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\Preload\Fonts;

/**
 * @covers \WP_Rocket\Engine\Preload\Fonts::preload_fonts
 * @group  Preload
 * @group  PreloadFonts
 */
class Test_PreloadFonts extends TestCase {
	private $options;
	private $cdn;
	private $fonts;

	public function setUp() {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn     = Mockery::mock( CDN::class );
		$this->fonts   = new Fonts( $this->options, $this->cdn );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddPreloadTagsWhenValidFonts( $rocket_options, $expected ) {
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'untrailingslashit' )->alias( function( $thing ) {
			return rtrim( $thing, '\/' );
		} );
		Functions\when( 'esc_url' )->returnArg();
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );

		$this->options->shouldReceive( 'get' )
			->with( 'preload_fonts', [] )
			->andReturn( $rocket_options['preload_fonts'] );

		$this->cdn->shouldReceive( 'rewrite_url' )
			->andReturnUsing( function( $url ) use ( $rocket_options ) {
				if ( $rocket_options['cdn'] ) {
					return str_replace( 'http://example.org', 'https://123456.rocketcdn.me', $url );
				}

				return $url;
			} );

		ob_start();
		$this->fonts->preload_fonts();
		$out = ob_get_contents();
		ob_end_clean();

		$this->assertSame( $this->format_the_html( $expected ), $this->format_the_html( $out ) );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preloadFonts' );
	}
}
