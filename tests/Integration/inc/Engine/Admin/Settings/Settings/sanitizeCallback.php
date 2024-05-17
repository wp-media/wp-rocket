<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Settings\Settings;

use WPMedia\PHPUnit\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\DBTrait;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Settings::sanitize_callback
 * Test class covering ::rocket_validate_css
 *
 * @group  AdminOnly
 * @group  Settings
 */
class Test_SanitizeCallback extends AdminTestCase {
	use DBTrait;

	public function set_up() {
		parent::set_up();

		self::removeDBHooks();
		$this->fireAdminInit();
	}

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

	/**
	 * @dataProvider addExcludeCSSProvider
	 */
	public function testShouldSanitizeExcludeCSS( $original, $sanitized ) {
		$actual = apply_filters( 'sanitize_option_wp_rocket_settings', $original );
		$this->assertSame(
			array_values( $sanitized['exclude_css'] ),
			array_values( $actual['exclude_css'] )
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
