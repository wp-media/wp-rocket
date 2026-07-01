<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::maybe_set_rocketcdn_as_cdn_type_on_upgrade
 *
 * @group CDN
 * @group RocketCDN
 * @group AdminOnly
 */
class Test_MaybeSetRocketcdnAsCdnTypeOnUpgrade extends TestCase {

	protected static $transients = [
		'rocketcdn_status' => null,
	];

	public function set_up(): void {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'wp_rocket_upgrade', 'maybe_set_rocketcdn_as_cdn_type_on_upgrade', 12 );
	}

	public function tear_down(): void {
		delete_transient( 'rocketcdn_status' );
		$this->restoreWpHook( 'wp_rocket_upgrade' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		if ( isset( $config['settings'] ) ) {
			$current = get_option( 'wp_rocket_settings', [] );
			update_option( 'wp_rocket_settings', array_merge( $current, $config['settings'] ) );
		}

		if ( isset( $config['rocketcdn_status'] ) ) {
			set_transient( 'rocketcdn_status', $config['rocketcdn_status'] );
		}

		do_action( 'wp_rocket_upgrade', $config['new_version'], $config['old_version'] );

		$settings = get_option( 'wp_rocket_settings', [] );
		$this->assertSame( $expected['cdn_type'], $settings['cdn_type'] ?? '' );

		if ( array_key_exists( 'cdn', $expected ) ) {
			$this->assertSame( $expected['cdn'], $settings['cdn'] ?? 0 );
		}

		if ( array_key_exists( 'cdn_cnames', $expected ) ) {
			$this->assertSame( $expected['cdn_cnames'], $settings['cdn_cnames'] ?? [] );
		}
	}
}
