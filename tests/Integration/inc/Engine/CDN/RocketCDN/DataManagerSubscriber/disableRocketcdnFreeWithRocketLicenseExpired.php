<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase as RocketCDNTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::disable_rocketcdn_free_with_rocket_license_expired
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_DisableRocketcdnFreeWithRocketLicenseExpired extends RocketCDNTestCase {
	/**
	 * DataManagerSubscriber instance.
	 *
	 * @var DataManagerSubscriber
	 */
	private $subscriber;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'rocketcdn_data_manager_subscriber' );
	}

	/**
	 * Restore state changed by the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		delete_option( 'wp_rocket_' . DataManagerSubscriber::FORCED_OFF_BY_LICENCE_EXPIRY_OPTION );

		parent::tear_down();
	}

	/**
	 * Only RocketCDN Free actually running should be force-disabled - not an unrelated
	 * BYOCDN/paid configuration, and not a state the user already set to 'nothing'
	 * themselves (that would trip the tracking flag for no reason).
	 *
	 * @dataProvider configTestData
	 *
	 * @param string $initial_cdn_state cdn_state stored before the call.
	 * @param bool   $should_disable    Whether the call is expected to force it to 'nothing'.
	 */
	public function testShouldDisableOnlyWhenRocketcdnFreeIsApplied( $initial_cdn_state, $should_disable ) {
		$settings              = get_option( 'wp_rocket_settings', [] );
		$settings['cdn_state'] = $initial_cdn_state;
		update_option( 'wp_rocket_settings', $settings );

		$this->subscriber->disable_rocketcdn_free_with_rocket_license_expired();

		$settings = get_option( 'wp_rocket_settings' );

		if ( $should_disable ) {
			$this->assertSame( 'nothing', $settings['cdn_state'] );
			$this->assertTrue( (bool) get_option( 'wp_rocket_' . DataManagerSubscriber::FORCED_OFF_BY_LICENCE_EXPIRY_OPTION ) );

			return;
		}

		$this->assertSame( $initial_cdn_state, $settings['cdn_state'] );
		$this->assertFalse( (bool) get_option( 'wp_rocket_' . DataManagerSubscriber::FORCED_OFF_BY_LICENCE_EXPIRY_OPTION ) );
	}

	/**
	 * Data provider for testShouldDisableOnlyWhenRocketcdnFreeIsApplied().
	 *
	 * @return array
	 */
	public function configTestData() {
		return [
			'rocketcdnFreeGetsDisabled' => [ 'rocketcdn_free', true ],
			'byocdnIsLeftAlone'         => [ 'byocdn', false ],
			'rocketcdnPaidIsLeftAlone'  => [ 'rocketcdn_paid', false ],
			'nothingIsLeftAlone'        => [ 'nothing', false ],
		];
	}
}
