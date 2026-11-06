<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Admin\Settings\Settings;

use Mockery;
use Brain\Monkey\Functions;
use WP_Rocket\Tests\StubTrait;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Settings\Settings;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Admin\Settings\Settings::sanitize_callback
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
		              ->withAnyArgs()
		              ->byDefault();

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

	/**
	 * Regression test: a caller that explicitly sets 'cdn' on the array passed to
	 * update_option() (e.g. CDNOptionsManager::disable()/enable(), whose write triggers
	 * this callback via register_setting()'s sanitize_option_wp_rocket_settings filter)
	 * must have that value respected, not silently reverted to the stale Options_Data
	 * snapshot's current value.
	 */
	public function testShouldPreserveExplicitlySubmittedCdnValue() {
		// Stale snapshot deliberately disagrees with the submitted value, so the
		// assertion can only pass if the submitted value actually wins.
		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( 1 );

		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$output = $this->settings->sanitize_callback( [ 'cdn' => 0 ] );

		$this->assertSame( 0, $output['cdn'] );
	}

	/**
	 * When 'cdn' isn't part of the submitted array at all (e.g. a general settings-form
	 * save, which has no 'cdn' field), the current stored value must be preserved.
	 */
	public function testShouldFallBackToStoredCdnValueWhenNotSubmitted() {
		$this->options->shouldReceive( 'get' )
			->with( 'cdn', 0 )
			->andReturn( 1 );

		Functions\when( 'rocket_valid_key' )->justReturn( true );

		$output = $this->settings->sanitize_callback( [] );

		$this->assertSame( 1, $output['cdn'] );
	}

	/**
	 * @dataProvider settingsSavedNoticeProvider
	 */
	public function testShouldAddSettingsSavedNoticeOnlyOnce( $global_errors, $transient_errors, $should_add ) {
		global $wp_settings_errors;

		$wp_settings_errors = $global_errors;

		Functions\when( 'rocket_valid_key' )->justReturn( true );
		Functions\when( 'get_transient' )->justReturn( $transient_errors );
		Functions\when( '__' )->returnArg();

		if ( $should_add ) {
			Functions\expect( 'add_settings_error' )
				->once()
				->with( 'general', 'settings_updated', 'Settings saved.', 'updated' );
		} else {
			Functions\expect( 'add_settings_error' )->never();
		}

		$this->settings->sanitize_callback( [ 'secret_key' => 'secret' ] );

		$wp_settings_errors = [];
	}

	public function settingsSavedNoticeProvider() {
		$wpr_notice  = [
			'setting' => 'general',
			'code'    => 'settings_updated',
			'message' => 'Settings saved.',
			'type'    => 'updated',
		];
		$core_notice = array_merge( $wpr_notice, [ 'type' => 'success' ] );

		return [
			'no notice queued yet'                    => [ [], false, true ],
			'WP Rocket notice queued in this request' => [ [ $wpr_notice ], false, false ],
			'WP Rocket notice persisted for redirect' => [ [], [ $wpr_notice ], false ],
			'core notice persisted for redirect'      => [ [], [ $core_notice ], false ],
			'core notice queued in this request'      => [ [ $core_notice ], false, false ],
		];
	}
}
