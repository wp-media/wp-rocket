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
	private $display;
	private $disable_preload;

	protected $config;

	public function set_up() {
		parent::set_up();

		$GLOBALS['wp'] = (object) [
			'query_vars' => [],
			'request'    => 'http://example.org',
			'public_query_vars' => [
				'embed',
			],
        ];

        $this->unregisterAllCallbacksExcept('rocket_buffer', 'process', 17 );
		add_filter('rocket_exclude_locally_host_fonts', [ $this, 'exclude_locally_host_fonts' ] ); // @phpstan-ignore-line
	}

	public function tear_down() {
		remove_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'return_true' ] );
		remove_filter( 'rocket_combined_google_fonts_display', [ $this, 'set_display_value' ] );
		remove_filter( 'rocket_disable_google_fonts_preload', [ $this, 'set_disable_preload' ] );
		remove_filter('rocket_exclude_locally_host_fonts', [ $this, 'exclude_locally_host_fonts' ] );


		$this->restoreWpHook('rocket_buffer');

		parent::tear_down();
	}

	/**
     * @dataProvider addDataProviderV1
     */
	public function testShouldCombineGoogleFontsV1( $config, $original, $combined ) {
		$this->config = $config;
		$this->doTest( $config, $original, $combined );
	}

	/**
     * @dataProvider addDataProviderV2
     */
	public function testShouldCombineGoogleFontsV2( $config, $original, $combined ) {
		$this->config = $config;
		$this->doTest( $config, $original, $combined );
	}

	/**
     * @dataProvider addDataProviderV1V2
     */
	public function testShouldCombineGoogleFontsV1V2( $config, $original, $combined ) {
		$this->config = $config;
		$this->doTest( $config, $original, $combined );
	}

	private function doTest( $config, $original, $expected ) {
		$this->display = $config['swap'];
		$this->disable_preload = $config['disable_preload'];

		add_filter( 'pre_get_rocket_option_minify_google_fonts', [ $this, 'return_true' ] );

		if ( false !== $config['swap'] ) {
			add_filter( 'rocket_combined_google_fonts_display', [ $this, 'set_display_value' ] );
		}

		add_filter( 'rocket_disable_google_fonts_preload', [ $this, 'set_disable_preload' ] );

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

	public function set_display_value() {
		return $this->display;
	}

	public function set_disable_preload() {
		return $this->disable_preload;
	}

	public function exclude_locally_host_fonts() {
		return $this->config['exclude_locally_host_fonts'] ?? [];
	}
}
