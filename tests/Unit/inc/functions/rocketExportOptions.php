<?php

namespace WP_Rocket\Tests\Unit\inc\functions;

use Brain\Monkey\Functions;
use WPMedia\PHPUnit\Unit\TestCase;

/**
 * Test class covering ::rocket_export_options
 *
 * @group Functions
 * @group Options
 */
class Test_RocketExportOptions extends TestCase {
	/**
	 * Load tested functions file.
	 *
	 * @return void
	 */
	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		if ( ! defined( 'WP_ROCKET_SLUG' ) ) {
			define( 'WP_ROCKET_SLUG', 'wp_rocket_settings' );
		}

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/functions/options.php';
	}

	/**
	 * Checks export payload when Mixpanel opt-in is enabled.
	 *
	 * @return void
	 */
	public function testShouldExportAnalyticsOptinWhenEnabled() {
		Functions\expect( 'get_home_url' )
			->once()
			->andReturn( 'https://example.org' );
		Functions\expect( 'get_rocket_parse_url' )
			->once()
			->with( 'https://example.org' )
			->andReturn(
				[
					'host' => 'example.org',
					'path' => '',
				]
			);
		Functions\expect( 'get_option' )
			->once()
			->with( 'wp_rocket_settings' )
			->andReturn(
				[
					'cache_mobile' => 1,
				]
			);
		Functions\expect( 'get_option' )
			->once()
			->with( 'rocket_mixpanel_optin', 0 )
			->andReturn( 1 );
		Functions\expect( 'wp_json_encode' )
			->once()
			->with(
				[
					'cache_mobile'      => 1,
					'analytics_enabled' => 1,
				],
				JSON_PRETTY_PRINT
			)
			->andReturn( '{"cache_mobile":1,"analytics_enabled":1}' );

		$result = rocket_export_options();

		$this->assertStringStartsWith( 'wp-rocket-settings-example.org-', $result[0] );
		$this->assertStringEndsWith( '.json', $result[0] );
		$this->assertSame( '{"cache_mobile":1,"analytics_enabled":1}', $result[1] );
	}

	/**
	 * Checks export payload when stored settings are not a valid array.
	 *
	 * @return void
	 */
	public function testShouldExportAnalyticsOptinWhenSettingsAreInvalid() {
		Functions\expect( 'get_home_url' )
			->once()
			->andReturn( 'https://example.org' );
		Functions\expect( 'get_rocket_parse_url' )
			->once()
			->with( 'https://example.org' )
			->andReturn(
				[
					'host' => 'example.org',
					'path' => '',
				]
			);
		Functions\expect( 'get_option' )
			->once()
			->with( 'wp_rocket_settings' )
			->andReturn( false );
		Functions\expect( 'get_option' )
			->once()
			->with( 'rocket_mixpanel_optin', 0 )
			->andReturn( 0 );
		Functions\expect( 'wp_json_encode' )
			->once()
			->with(
				[
					'analytics_enabled' => 0,
				],
				JSON_PRETTY_PRINT
			)
			->andReturn( '{"analytics_enabled":0}' );

		$result = rocket_export_options();

		$this->assertStringStartsWith( 'wp-rocket-settings-example.org-', $result[0] );
		$this->assertStringEndsWith( '.json', $result[0] );
		$this->assertSame( '{"analytics_enabled":0}', $result[1] );
	}
}
