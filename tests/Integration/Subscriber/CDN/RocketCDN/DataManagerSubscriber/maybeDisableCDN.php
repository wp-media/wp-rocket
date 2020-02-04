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

	public function testShouldReturnScheduleNewCheckEventWhenSubscriptionRunning() {
		do_action( 'rocketcdn_check_subscription_status_event' );
	}

	public function testShouldDisableCDNWhenSubscriptionCancelled() {
		do_action( 'rocketcdn_check_subscription_status_event' );
	}
}
