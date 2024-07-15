<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Fonts;

use Brain\Monkey\Filters;
use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\Preload\Fonts;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Preload\Fonts::preload_fonts
 * @group  Preload
 * @group  PreloadFonts
 */
class Test_PreloadFonts extends TestCase {
	private $options;
	private $cdn;
	private $fonts;

	public function setUp() : void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->cdn     = Mockery::mock( CDN::class );
		$this->fonts   = new Fonts( $this->options, $this->cdn );

		Functions\stubEscapeFunctions();
		$this->stubWpParseUrl();
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddPreloadTagsWhenValidFonts( $buffer, $bypass, $filter, $rocket_options, $expected ) {
		Functions\when( 'rocket_bypass' )->justReturn( $bypass );
		Functions\when( 'home_url' )->justReturn( 'http://example.org' );

		if ( $filter ) {
			Filters\expectApplied( 'rocket_disable_preload_fonts' )
			->once()
			->andReturn( $filter );
		}

		if ( ! $bypass ) {
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
		}

		$this->assertSame(
			$expected,
			$this->fonts->preload_fonts( $buffer )
		);
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preloadFonts' );
	}
}
