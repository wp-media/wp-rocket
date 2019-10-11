<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDNSubscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDNSubscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

class TestValidateEmail extends TestCase {
    public function testShouldReturnTrueWhenEmailIsValid() {
        update_option(
            'wp_rocket_settings',
            [
                'consumer_email' => 'dummy@wp-rocket.me',
            ]
        );

        $request     = new \WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $options_api = new Options( 'wp_rocket_' );
        $options     = new Options_Data( $options_api->get( 'settings' ) );
        $rocketcdn   = new RocketCDNSubscriber( $options_api, $options );

        $this->assertTrue( $rocketcdn->validate_email( 'dummy@wp-rocket.me', $request, 'email' ) );
    }

    public function testShouldReturnFalseWhenEmailIsInvalid() {
        update_option(
            'wp_rocket_settings',
            [
                'consumer_email' => 'dummy@wp-rocket.me',
            ]
        );

        $request     = new \WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $options_api = new Options( 'wp_rocket_' );
        $options     = new Options_Data( $options_api->get( 'settings' ) );
        $rocketcdn   = new RocketCDNSubscriber( $options_api, $options );

        $this->assertFalse( $rocketcdn->validate_email( 'nulled@wp-rocket.me', $request, 'email' ) );
    }
}
