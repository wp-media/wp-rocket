<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Preload\Fonts;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Fonts;

/**
 * @covers \WP_Rocket\Engine\Preload\Fonts::preload_fonts
 * @group  Preload
 */
class Test_PreloadFonts extends TestCase {

	public function testShouldNotAddPreloadTagsWhenInvalidFonts() {
		$options = $this->createMock( Options_Data::class );
		$options->method( 'get' )
			->with( 'preload_fonts', [] )
			->willReturnOnConsecutiveCalls( [], [ '/wp-content/style.css', '/wp-content/style.js' ], [ '/test.eot' ] );

		$preload = new Fonts( $options );

		Functions\expect( 'untrailingslashit' )->never();
		Functions\expect( 'get_option' )->never();

		ob_start();
		$preload->preload_fonts();
		$out = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( $out );

		ob_start();
		$preload->preload_fonts();
		$out = ob_get_contents();
		ob_end_clean();

		$this->assertEmpty( $out );
	}

	public function testShouldAddPreloadTagsWhenValidFonts() {
		$data = $this->getData();

		Functions\when( 'wp_parse_url' )->alias( function( $url, $component = -1 ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'untrailingslashit' )->alias( function( $thing ) {
			return rtrim( $thing, '\/' );
		} );
		Functions\when( 'esc_url' )->returnArg();
		Functions\expect( 'get_option' )
			->with( 'home' )
			->andReturn( 'http://example.org' );

		$options = $this->createMock( Options_Data::class );
		$options->method( 'get' )
			->with( 'preload_fonts', [] )
			->willReturn( $data['input'] );

		$preload = new Fonts( $options );

		ob_start();
		$preload->preload_fonts();
		$out = ob_get_contents();
		ob_end_clean();

		$this->assertSame( $data['expected'], $out );
	}

	private function getData() {
		$out = [
			'input'    => [
				'/wp-content/file.dfont',
				'',
				'/wp-content/file.eot',
				'/wp-content/file.otc',
				'/wp-content/file.otf',
				'/wp-content/file.ott',
				'/wp-content/file.ttc',
				'/wp-content/file.tte',
				'/wp-content/file.ttf',
				'/wp-content/file.svg',
				'/wp-content/file.woff',
				'/wp-content/file.woff2',
				'/wp-content/file.woff2',
				'/wp-content/file.css',
				'/wp-content/file.js',
			],
			'expected' => '',
		];

		$expected_array = [
			'/wp-content/file.otf',
			'/wp-content/file.ttf',
			'/wp-content/file.svg',
			'/wp-content/file.woff',
			'/wp-content/file.woff2',
		];

		foreach ( $expected_array as $font ) {
			$out['expected'] .= sprintf(
				"\n<link rel=\"preload\" as=\"font\" href=\"http://example.org%s\" crossorigin>",
				$font
			);
		}

		return $out;
	}
}
