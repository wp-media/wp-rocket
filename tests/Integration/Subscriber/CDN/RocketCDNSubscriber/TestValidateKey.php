<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDNSubscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDNSubscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

class TestValidateKey extends TestCase {
    public function testShouldReturnTrueWhenKeyIsValid() {
        update_option(
            'wp_rocket_settings',
            [
                'consumer_key' => '0123456',
            ]
        );

        $request     = new \WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $options_api = new Options( 'wp_rocket_' );
        $options     = new Options_Data( $options_api->get( 'settings' ) );
        $rocketcdn   = new RocketCDNSubscriber( $options_api, $options );

        $this->assertTrue( $rocketcdn->validate_key( '0123456', $request, 'key' ) );
    }

    public function testShouldReturnFalseWhenKeylIsInvalid() {
        update_option(
            'wp_rocket_settings',
            [
                'consumer_key' => '0123456',
            ]
        );

        $request     = new \WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $options_api = new Options( 'wp_rocket_' );
        $options     = new Options_Data( $options_api->get( 'settings' ) );
        $rocketcdn   = new RocketCDNSubscriber( $options_api, $options );

        $this->assertFalse( $rocketcdn->validate_key( '000000', $request, 'key' ) );
    }
}