<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\DataManagerSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\DataManagerSubscriber::maybe_update_api_status
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_MaybeUpdateAPIStatus extends TestCase {
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

    public function testShouldDoNothingWhenMissingIndex() {
        add_option( 'wp_rocket_settings', [] );

        $before_response = $this->get_website_data();

        update_option( 'wp_rocket_settings', [
            'minify_css' => 1,
        ] );

        $after_response = $this->get_website_data();

        $this->assertSame( $before_response->is_active, $after_response->is_active );
    }

    public function testShouldDoNothingWhenCDNIsSameValue() {
        add_option( 'wp_rocket_settings', [
            'cdn' => 1,
        ] );

        $before_response = $this->get_website_data();

        update_option( 'wp_rocket_settings', [
            'cdn' => 1,
        ] );

        $after_response = $this->get_website_data();

        $this->assertSame( $before_response->is_active, $after_response->is_active );
    }

    public function testShouldSendUpdateRequestWhenCDNIsNotSameValue() {
        add_option( 'wp_rocket_settings', [
            'cdn' => 0,
        ] );

        update_option( 'wp_rocket_settings', [
            'cdn' => 1,
        ] );

        $response = $this->get_website_data();

        $this->assertTrue( $response->is_active );
	}

    private function get_website_data() {
        $response = wp_remote_get(
			'https://rocketcdn.me/api/website/search/?url=http://example.org',
			[
				'headers' => [
					'Authorization' => 'Token ' . self::getApiCredential( 'ROCKETCDN_TOKEN' ),
				],
			]
        );

        return json_decode( wp_remote_retrieve_body( $response ) );
    }
}
