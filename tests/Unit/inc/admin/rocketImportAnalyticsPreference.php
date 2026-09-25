<?php

namespace WP_Rocket\Tests\Unit\inc\admin;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_import_analytics_preference
 *
 * @group admin
 * @group Options
 */
class Test_RocketImportAnalyticsPreference extends TestCase {
	/**
	 * Load tested file and bootstrap expected constants/functions.
	 *
	 * @return void
	 */
	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_ROCKET_FILE' ) ) {
			define( 'WP_ROCKET_FILE', '/tmp/wp-rocket/wp-rocket.php' );
		}

		Functions\when( 'plugin_basename' )->justReturn( 'wp-rocket/wp-rocket.php' );

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/admin.php';
	}

	/**
	 * Checks settings remain unchanged when legacy key is missing.
	 *
	 * @return void
	 */
	public function testShouldReturnSettingsUnchangedWhenLegacyOptionMissing() {
		$settings = [
			'cache_mobile' => 1,
		];

		Functions\expect( 'get_option' )->never();
		Functions\expect( 'update_option' )->never();
		Functions\expect( 'do_action' )->never();

		// @phpstan-ignore-next-line
		$this->assertSame( $settings, rocket_import_analytics_preference( $settings ) );
	}

	/**
	 * Checks legacy analytics option syncs Mixpanel opt-in value.
	 *
	 * @dataProvider analyticsImportProvider
	 *
	 * @param array $settings      Imported settings payload.
	 * @param int   $current_optin Current Mixpanel opt-in value.
	 * @param bool  $should_update Whether update_option() should be called.
	 * @param int   $expected_optin Expected Mixpanel opt-in value.
	 *
	 * @return void
	 */
	public function testShouldSyncMixpanelOptinFromLegacySetting( array $settings, int $current_optin, bool $should_update, int $expected_optin ) {
		Functions\expect( 'get_option' )
			->once()
			->with( 'rocket_mixpanel_optin', 0 )
			->andReturn( $current_optin );

		if ( $should_update ) {
			Functions\expect( 'update_option' )
				->once()
				->with( 'rocket_mixpanel_optin', $expected_optin );
			Functions\expect( 'do_action' )
				->once()
				->with( 'rocket_mixpanel_optin_changed', 1 === $expected_optin );
		} else {
			Functions\expect( 'update_option' )->never();
			Functions\expect( 'do_action' )->never();
		}

		// @phpstan-ignore-next-line
		$result = rocket_import_analytics_preference( $settings );

		$this->assertArrayNotHasKey( 'analytics_enabled', $result );
	}

	/**
	 * Provides scenarios for legacy analytics import handling.
	 *
	 * @return array
	 */
	public function analyticsImportProvider(): array {
		return [
			'should enable optin from legacy option'    => [
				'settings'       => [
					'analytics_enabled' => 1,
				],
				'current_optin'  => 0,
				'should_update'  => true,
				'expected_optin' => 1,
			],
			'should disable optin from legacy option'   => [
				'settings'       => [
					'analytics_enabled' => 0,
				],
				'current_optin'  => 1,
				'should_update'  => true,
				'expected_optin' => 0,
			],
			'should not update when value is unchanged' => [
				'settings'       => [
					'analytics_enabled' => 1,
				],
				'current_optin'  => 1,
				'should_update'  => false,
				'expected_optin' => 1,
			],
		];
	}
}
