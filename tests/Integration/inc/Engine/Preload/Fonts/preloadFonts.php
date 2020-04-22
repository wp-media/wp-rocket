<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Fonts;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Fonts;

/**
 * @covers \WP_Rocket\Engine\Preload\Fonts::preload_fonts
 * @group  Preload
 */
class Test_PreloadFonts extends TestCase {

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_empty_array' ] );
		remove_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_option_data' ] );
	}

	public function testShouldNotAddPreloadTagsWhenInvalidFonts() {
		add_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_empty_array' ] );

		$expected = $this->format_the_html( $this->getExpected() );

		ob_start();
		do_action( 'wp_head' );
		$out = ob_get_contents();
		ob_end_clean();

		$this->assertStringNotContainsString( $expected, $this->format_the_html( $out ) );

		ob_start();
		do_action( 'wp_head' );
		$out = ob_get_contents();
		ob_end_clean();

		$this->assertStringNotContainsString( $expected, $this->format_the_html( $out ) );
	}

	public function testShouldAddPreloadTagsWhenValidFonts() {
		add_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_option_data' ] );

		ob_start();
		do_action( 'wp_head' );
		$out = ob_get_contents();
		ob_end_clean();

		$this->assertStringContainsString( $this->format_the_html( $this->getExpected() ), $this->format_the_html( $out ) );
	}

	public function return_empty_array() {
		return [];
	}

	public function return_option_data() {
		return [
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
		];
	}

	private function getExpected() {
		$expected       = '';
		$expected_array = [
			'/wp-content/file.otf',
			'/wp-content/file.ttf',
			'/wp-content/file.svg',
			'/wp-content/file.woff',
			'/wp-content/file.woff2',
		];

		foreach ( $expected_array as $font ) {
			$expected .= sprintf(
				"\n<link rel=\"preload\" as=\"font\" href=\"http://example.org%s\" crossorigin>",
				$font
			);
		}

		return $expected;
	}
}
