<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Preload\Fonts;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Preload\Fonts::preload_fonts
 *
 * @group  Preload
 * @group  PreloadFonts
 */
class Test_PreloadFonts extends TestCase {
	protected $wp_head;
	private   $preload_fonts;
	private   $cdn;
	private   $cnames;

	public function setUp() {
		parent::setUp();

		$this->unregisterAllCallbacksExcept( 'wp_head', 'preload_fonts', 10 );
	}

	public function tearDown() {
		$this->restoreWpFilter( 'wp_head' );

		parent::tearDown();

		remove_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_preload_fonts' ] );
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_cdn' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'return_cdn_cnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'return_cdn_zones' ] );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddPreloadTagsWhenValidFonts( $rocket_options, $expected ) {
		$this->setUpOptionsAndHooks( $rocket_options );

		ob_start();
		do_action( 'wp_head' );
		$output = ob_get_clean();

		$actual = $this->format_the_html( $output );

		if ( empty( $expected ) ) {
			$this->assertNotContains(
				'<link rel="preload" as="font"',
				$actual
			);

		} else {
			$this->assertContains(
				$this->format_the_html( $expected ),
				$actual
			);
		}
	}

	protected function setUpOptionsAndHooks( $rocket_options ) {
		$this->preload_fonts = $rocket_options['preload_fonts'];
		$this->cdn           = $rocket_options['cdn'];
		$this->cnames        = $rocket_options['cdn_cnames'];

		add_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_preload_fonts' ] );
		add_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_cdn' ] );
		add_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'return_cdn_cnames' ] );
		add_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'return_cdn_zones' ] );
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
