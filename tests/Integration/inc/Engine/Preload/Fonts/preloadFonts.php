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
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_1' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'return_cdn_cnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'return_cdn_zones' ] );
	}

	public function testShouldNotAddPreloadTagsWhenInvalidFonts() {
		add_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_empty_array' ] );

		$expected      = $this->format_the_html( $this->getExpected() );
		$assert_exists = method_exists( $this, 'assertStringNotContainsString' );

		ob_start();
		do_action( 'wp_head' );
		$out = ob_get_contents();
		ob_end_clean();

		if ( $assert_exists ) {
			$this->assertStringNotContainsString( $expected, $this->format_the_html( $out ) );
		} else {
			$this->assertFalse( mb_strpos( $this->format_the_html( $out ), $expected ) );
		}

		ob_start();
		do_action( 'wp_head' );
		$out = ob_get_contents();
		ob_end_clean();

		if ( $assert_exists ) {
			$this->assertStringNotContainsString( $expected, $this->format_the_html( $out ) );
		} else {
			$this->assertFalse( mb_strpos( $this->format_the_html( $out ), $expected ) );
		}
	}

	public function testShouldAddPreloadTagsWhenValidFonts() {
		add_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_option_data' ] );
		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_1' ] );
		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'return_cdn_cnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'return_cdn_zones' ] );

		ob_start();
		do_action( 'wp_head' );
		$out = ob_get_contents();
		ob_end_clean();

		if ( method_exists( $this, 'assertStringContainsString' ) ) {
			$this->assertStringContainsString( $this->format_the_html( $this->getExpected() ), $this->format_the_html( $out ) );
		} else {
			$this->assertNotFalse( mb_strpos( $this->format_the_html( $out ), $this->format_the_html( $this->getExpected() ) ) );
		}
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

	public function return_cdn_cnames() {
		return [
			'js.example.org',
			'images.example.org',
			'css.example.org',
			'cdn.example.org',
		];
	}

	public function return_cdn_zones() {
		return [
			'js',
			'images',
			'css',
			'all',
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
				"\n<link rel=\"preload\" as=\"font\" href=\"http://cdn.example.org%s\" crossorigin>",
				$font
			);
		}

		return $expected;
	}
}
