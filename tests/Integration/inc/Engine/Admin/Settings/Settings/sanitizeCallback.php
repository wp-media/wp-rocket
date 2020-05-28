<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Settings;

use WPMedia\PHPUnit\Integration\AdminTestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Settings\Settings::sanitize_callback
 * @group  AdminOnly
 * @group  Settings
 */
class Test_SanitizeCallback extends AdminTestCase {

	/**
	 * @dataProvider addDNSPrefetchProvider
	 */
	public function testShouldSanitizeDNSPrefetchEntries( $input, $expected ) {
		$output = apply_filters( 'sanitize_option_wp_rocket_settings', $input );

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
		$output = apply_filters( 'sanitize_option_wp_rocket_settings', $input );

		$this->assertArrayHasKey( 'preload_fonts', $output );
		$this->assertSame(
			$expected['preload_fonts'],
			array_values( $output['preload_fonts'] )
		);
	}

	/**
	 * @dataProvider addCriticalCSSProvider
	 */
	public function testShouldSanitizeCriticalCss( $original, $sanitized ) {
		$actual = apply_filters( 'sanitize_option_wp_rocket_settings', $original );
		$this->assertSame(
			$sanitized['critical_css'],
			$actual['critical_css']
		);
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
