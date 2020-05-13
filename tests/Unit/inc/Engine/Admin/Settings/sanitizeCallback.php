<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Settings;

use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings::sanitize_callback
 * @group  Admin
 * @group  Settings
 */
class Test_SanitizeCallback extends TestCase {
	private $options;
	private $settings;

	public function setUp() {
		parent::setUp();

		$this->options  = $this->createMock( Options::class );
		$this->settings = new Settings( $this->options );
	}

	/**
	 * @dataProvider addCriticalCSSProvider
	 */
	public function testShouldSanitizeCriticalCss( $original, $sanitized ) {
		Functions\when( 'wp_strip_all_tags' )->alias( function( $string, $remove_breaks ) {
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
		Functions\when( 'esc_url_raw' )->alias( function( $url ) {
			if ( false === strpos( $url, ':' ) ) {
				$url = 'http:' . $url;
			}

			return filter_var( $url, FILTER_VALIDATE_URL );
		} );
		Functions\when( 'wp_parse_url' )->alias( function( $url, $component ) {
			return parse_url( $url, $component );
		} );
		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$output = $this->settings->sanitize_callback( $input );

		$this->assertArrayHasKey( 'dns_prefetch', $output );
		$this->assertSame(
			$expected['dns_prefetch'],
			array_values( $output['dns_prefetch'] )
		);
	}

	public function addDNSPrefetchProvider() {
		return $this->getTestData( __DIR__, 'dns-prefetch' );
	}

	public function addCriticalCSSProvider() {
		return $this->getTestData( __DIR__, 'sanitizeCallback' );
	}
}
