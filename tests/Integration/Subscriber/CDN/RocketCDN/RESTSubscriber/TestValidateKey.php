<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

/**
 * @coversDefaultClass \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber;
 */
class TestValidateKey extends TestCase {
    /**
	 * @covers ::validate_key
	 * @group RocketCDN
	 */
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
        $rocketcdn   = new RESTSubscriber( $options_api, $options );

        $this->assertTrue( $rocketcdn->validate_key( '0123456', $request, 'key' ) );
    }

    /**
	 * @covers ::validate_key
	 * @group RocketCDN
	 */
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
        $rocketcdn   = new RESTSubscriber( $options_api, $options );

        $this->assertFalse( $rocketcdn->validate_key( '000000', $request, 'key' ) );
    }
}