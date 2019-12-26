<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN\RESTSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rest_Request;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\RESTSubscriber::enable
 * @group RocketCDN
 */
class TestEnable extends TestCase {
    /**
     * Test that the WPR options array is correctly updated after enabling RocketCDN
     */
    public function testWPRocketOptionsUpdated() {
        $request = new WP_Rest_Request( 'PUT', '/wp-rocket/v1/rocketcdn/enable' );
        $request->set_body_params(
            [
                'url' => 'https://rocketcdn.me',
            ]
        );

        $options_api = new Options( 'wp_rocket_' );
        $options     = new Options_Data( $options_api->get( 'settings' ) );
        $rocketcdn   = new RESTSubscriber( $options_api, $options );
        $rocketcdn->enable( $request );

        $wp_rocket_settings = get_option( 'wp_rocket_settings' );

        $this->assertSame(
            1,
            $wp_rocket_settings['cdn']
        );

        $this->assertContains(
            'https://rocketcdn.me',
            $wp_rocket_settings['cdn_cnames']
        );

        $this->assertContains(
            'all',
            $wp_rocket_settings['cdn_zone']
        );
    }
}