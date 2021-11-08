<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Settings;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\StubTrait;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Settings::sanitize_callback
 * @group  Admin
 * @group  Settings
 */
class Test_SanitizeCallback extends TestCase {
	use StubTrait;

	private $options;
	private $settings;

	public function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->options->shouldReceive( 'get' )
		              ->withAnyArgs();

		$this->settings = new Settings( $this->options );
	}

	/**
	 * @dataProvider addCriticalCSSProvider
	 */
	public function testShouldSanitizeCriticalCss( $original, $sanitized ) {
		Functions\when( 'wp_strip_all_tags' )->alias( function ( $string, $remove_breaks ) {
			$string = preg_replace( '@<(script|style)[^>]*?>.*?</\\1>@si', '', $string );
			$string = strip_tags( $string );

			if ( $remove_breaks ) {
				$string = preg_replace( '/[\r\n\t ]+/', ' ', $string );
			}

			return trim( $string );
		} );

		Functions\when( 'sanitize_email' )->returnArg();

		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$sanitize_callback = $this->settings->sanitize_callback( $original );

		// this works
		$this->assertSame(
			$sanitized['critical_css'],
			$sanitize_callback['critical_css']
		);
	}

	/**
	 * @dataProvider addDNSPrefetchProvider
	 */
	public function testShouldSanitizeDNSPrefetchEntries( $input, $expected ) {
		Functions\when( 'esc_url_raw' )->alias( function ( $url ) {
			if ( false === strpos( $url, ':' ) ) {
				$url = 'http:' . $url;
			}

			return filter_var( $url, FILTER_VALIDATE_URL );
		} );
		$this->stubWpParseUrl();
		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$output = $this->settings->sanitize_callback( $input );

		$this->assertArrayHasKey( 'dns_prefetch', $output );
		$this->assertSame(
			$expected['dns_prefetch'],
			array_values( $output['dns_prefetch'] )
		);
	}

	/**
	 * @dataProvider addFontPreloadProvider
	 */
	public function testShouldSanitizeFontPreloadEntries( $input, $expected ) {
		Functions\when( 'rocket_valid_key' )->justReturn( true );
		$this->stubWpParseUrl();
		Functions\expect( 'home_url' )
			->andReturn( 'http://example.org' );

		$output = $this->settings->sanitize_callback( $input );

		$this->assertArrayHasKey( 'preload_fonts', $output );
		$this->assertSame(
			$expected['preload_fonts'],
			array_values( $output['preload_fonts'] )
		);
	}

	/**
	 * @dataProvider addExcludeCSSProvider
	 */
	public function testShouldSanitizeExcludeCSS( $original, $sanitized ) {
		$this->stubWpParseUrl();

		Functions\when( 'rocket_validate_css' )->alias( function ( $url ) {
			$file_host = parse_url( $url, PHP_URL_HOST );
			if ( 'example.org' === $file_host ) {
				return parse_url( trim( $url ), PHP_URL_PATH );
			}

			return str_replace( [ 'http://', 'https://' ], '', strtok( $url, '?' ) );
		} );

		Functions\when( 'sanitize_text_field' )->returnArg();
		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$sanitize_callback = $this->settings->sanitize_callback( $original );

		// this works
		$this->assertSame(
			array_values( $sanitized['exclude_css'] ),
			array_values( $sanitize_callback['exclude_css'] )
		);
	}

	public function addExcludeCSSProvider() {
		return $this->getTestData( __DIR__, 'exclude-css' );
	}

	public function addDNSPrefetchProvider() {
		return $this->getTestData( __DIR__, 'dns-prefetch' );
	}

	public function addFontPreloadProvider() {
		return $this->getTestData( __DIR__, 'font-preload' );
	}

	public function addCriticalCSSProvider() {
		return $this->getTestData( __DIR__, 'sanitizeCallback' );
	}
}
