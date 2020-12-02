<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts::optimize
 * @uses   \WP_Rocket\Logger\Logger
 * @group  Optimize
 * @group  CombineGoogleFonts
 */
class Test_Optimize extends TestCase {

	public function setUp() {
		parent::setUp();
		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org',
			'public_query_vars' => [
				'embed',
			],
        ];
	}

	public function tearDown() {
		remove_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'return_true' ] );
		parent::tearDown();
	}

	/**
     * @dataProvider addDataProviderV1
     */
	public function testShouldCombineGoogleFontsV1( $original, $combined ) {
		$this->doTest( $original, $combined );
	}

	/**
     * @dataProvider addDataProviderV2
     */
	public function testShouldCombineGoogleFontsV2( $original, $combined ) {
		$this->doTest( $original, $combined );
	}

	/**
     * @dataProvider addDataProviderV1V2
     */
	public function testShouldCombineGoogleFontsV1V2( $original, $combined ) {
		$this->doTest( $original, $combined );
	}

	private function doTest( $original, $combined ) {
		add_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'return_true' ] );

		$actual = apply_filters( 'rocket_buffer', $original );

		$this->assertSame(
			$combined,
			$actual
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
}
