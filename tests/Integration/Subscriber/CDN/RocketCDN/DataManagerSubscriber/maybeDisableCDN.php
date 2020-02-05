<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::maybe_disable_cdn
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_MaybeDisableCDN extends TestCase {
	protected static $api_credentials_config_file = 'rocketcdn.php';

    public function setUp() {
        parent::setUp();

        set_transient( 'rocketcdn_status', [ 'id' => self::getApiCredential( 'ROCKETCDN_WEBSITE_ID' ) ], MINUTE_IN_SECONDS );
		update_option( 'rocketcdn_user_token', self::getApiCredential( 'ROCKETCDN_TOKEN' ) );
    }

    public function tearDown() {
        parent::tearDown();

        delete_transient( 'rocketcdn_status' );
        delete_option( 'rocketcdn_user_token' );
        delete_option( 'wp_rocket_settings' );
	}

	public function testShouldScheduleNewCheckEventWhenSubscriptionRunning() {
        $expected_subset = [
			'cdn'        => 1,
			'cdn_cnames' => [ 'https://rocketcdn.me' ],
			'cdn_zone'   => [ 'all' ],
        ];

        update_option( 'wp_rocket_settings', $expected_subset );

        $callback = function( $subscription ) {
            if ( ! isset( $subscription['subscription_status'] ) || 'running' !== $subscription['subscription_status'] ) {
                $subscription['subscription_status'] = 'running';
            }

            return $subscription;
        };

        add_filter( 'rocket_pre_get_subscription_data', $callback );
        do_action( 'rocketcdn_check_subscription_status_event' );
        remove_filter( 'rocket_pre_get_subscription_data', $callback );

        $this->assertNotFalse( wp_next_scheduled( 'rocketcdn_check_subscription_status_event' ) );

        $options = get_option( 'wp_rocket_settings' );

		foreach ( $expected_subset as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[$key] );
		}
	}

	public function testShouldDisableCDNWhenSubscriptionCancelled() {
        $expected_subset = [
			'cdn'        => 0,
			'cdn_cnames' => [],
			'cdn_zone'   => [],
        ];

        update_option( 'wp_rocket_settings', [
			'cdn'        => 1,
			'cdn_cnames' => [ 'https://rocketcdn.me' ],
			'cdn_zone'   => [ 'all' ],
        ] );

        $callback = function( $subscription ) {
            if ( ! isset( $subscription['subscription_status'] ) || 'cancelled' !== $subscription['subscription_status'] ) {
                $subscription['subscription_status'] = 'cancelled';
            }

            return $subscription;
        };

		add_filter( 'rocket_pre_get_subscription_data', $callback );
        do_action( 'rocketcdn_check_subscription_status_event' );
        remove_filter( 'rocket_pre_get_subscription_data', $callback );

        $this->assertFalse( wp_next_scheduled( 'rocketcdn_check_subscription_status_event' ) );

        $options = get_option( 'wp_rocket_settings' );

		foreach ( $expected_subset as $key => $value ) {
			$this->assertArrayHasKey( $key, $options );
			$this->assertSame( $value, $options[$key] );
		}
	}
}
