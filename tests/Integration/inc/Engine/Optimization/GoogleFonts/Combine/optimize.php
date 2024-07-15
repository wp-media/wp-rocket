<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\GoogleFonts::optimize
 *
 * @uses   \WP_Rocket\Logger\Logger
 *
 * @group  Optimize
 * @group  GoogleFonts
 */
class Test_Optimize extends TestCase {

	private $filter_value;

	public function set_up() {
		parent::set_up();
		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org',
			'public_query_vars' => [
				'embed',
			],
        ];
		$this->unregisterAllCallbacksExcept('rocket_buffer', 'process', 1001 );
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'return_true' ] );
		remove_filter( 'rocket_combined_google_fonts_display', [ $this, 'set_display_value' ] );

		unset( $this->filter_value );
		$this->restoreWpHook('rocket_buffer');
		parent::tear_down();
	}

	/**
     * @dataProvider addDataProviderV1
     */
	public function testShouldCombineGoogleFontsV1( $original, $combined, $filtered = false ) {
		$this->doTest( $original, $combined, $filtered );
	}

	/**
     * @dataProvider addDataProviderV2
     */
	public function testShouldCombineGoogleFontsV2( $original, $combined, $filtered = false ) {
		$this->doTest( $original, $combined, $filtered );
	}

	/**
     * @dataProvider addDataProviderV1V2
     */
	public function testShouldCombineGoogleFontsV1V2( $original, $combined, $filtered = false ) {
		$this->doTest( $original, $combined, $filtered );
	}

	private function doTest( $original, $expected, $filtered ) {
		add_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'return_true' ] );

		if ( $filtered ) {
			$this->filter_value = $filtered;
			add_filter( 'rocket_combined_google_fonts_display', [ $this, 'set_display_value' ] );
		}

		$actual = apply_filters( 'rocket_buffer', $original );

		$this->assertSame(
			$this->format_the_html( $expected ),
			$this->format_the_html( $actual )
		);
	}

	public function addDataProviderV1() {
		return $this->getTestData( __DIR__, 'optimize' );
	}

	public function addDataProviderV2() {
		return $this->getTestData( __DIR__ . 'V2', 'optimize' );
	}

	public function addDataProviderV1V2() {
		return $this->getTestData( __DIR__ . 'V1V2', 'optimize' );
	}

	public function set_display_value( $filtered ) {
		return $this->filter_value;
	}
}
