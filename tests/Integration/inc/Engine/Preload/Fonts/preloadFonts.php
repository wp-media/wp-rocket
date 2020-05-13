<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Fonts;

use WPMedia\PHPUnit\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Preload\Fonts;

/**
 * @covers \WP_Rocket\Engine\Preload\Fonts::preload_fonts
 * @group  Preload
 * @group  PreloadFonts
 */
class Test_PreloadFonts extends TestCase {
	private $preload_fonts;
	private $cdn;
	private $cnames;

	public function tearDown() {
		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_preload_fonts' ] );
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_cdn' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'return_cdn_cnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'return_cdn_zones' ] );
	}

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldAddPreloadTagsWhenValidFonts( $rocket_options, $expected ) {
		$this->preload_fonts = $rocket_options['preload_fonts'];
		$this->cdn           = $rocket_options['cdn'];
		$this->cnames        = $rocket_options['cdn_cnames'];

		add_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_preload_fonts' ] );
		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_cdn' ] );
		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'return_cdn_cnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'return_cdn_zones' ] );

		ob_start();
		do_action( 'wp_head' );
		$out = ob_get_contents();
		ob_end_clean();

		if ( empty( $expected ) ) {
			$this->assertNotContains(
				'<link rel="preload" as="font"',
				$this->format_the_html( $out )
			);
		} else {
			$this->assertContains(
				$this->format_the_html( $expected ),
				$this->format_the_html( $out )
			);
		}
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preloadFonts' );
	}

	public function return_preload_fonts() {
		return $this->preload_fonts;
	}

	public function return_cdn() {
		return $this->cdn;
	}

	public function return_cdn_cnames() {
		return $this->cnames;
	}

	public function return_cdn_zones() {
		return [
			'all',
		];
	}
}
