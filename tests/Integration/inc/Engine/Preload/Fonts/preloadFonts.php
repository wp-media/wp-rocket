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
	private   $preload_fonts;
	private   $cdn;
	private   $cnames;

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_preload_fonts', [ $this, 'return_preload_fonts' ] );
		remove_filter( 'pre_get_rocket_option_cdn', [ $this, 'return_cdn' ] );
		remove_filter( 'pre_get_rocket_option_cdn_cnames', [ $this, 'return_cdn_cnames' ] );
		remove_filter( 'pre_get_rocket_option_cdn_zone', [ $this, 'return_cdn_zones' ] );
		remove_filter( 'rocket_disable_preload_fonts', [ $this, 'return_true' ] );

		unset( $GLOBALS['wp'] );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddPreloadTagsWhenValidFonts( $buffer, $bypass, $filter, $rocket_options, $expected ) {
		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org',
		];

		if ( $bypass ) {
			$GLOBALS['wp']->query_vars['nowprocket'] =  1;
		}

		if ( $filter ) {
			add_filter( 'rocket_disable_preload_fonts', [ $this, 'return_true' ] );
		}

		$this->setUpOptionsAndHooks( $rocket_options );

		$output = apply_filters( 'rocket_buffer', $buffer );

		$actual = $this->format_the_html( $output );

		$this->assertStringContainsString(
			$this->format_the_html( $expected ),
			$actual
		);
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
