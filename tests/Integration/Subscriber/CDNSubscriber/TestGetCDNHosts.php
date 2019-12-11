<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDNSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\CDN\CDN;
use WP_Rocket\Subscriber\CDN\CDNSubscriber;

class TestRewriteGetCDNHosts extends TestCase {
    public function testShouldReturnCDNHosts() {
        update_option(
            'wp_rocket_settings',
            [
                'cdn' => '1',
                'cdn_cnames' => [
                    'cdn.example.org'
                ],
                'cdn_zone' => [
                    'all'
                ],
                'cdn_reject_files' => [],
            ]
        );

        $options        = new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) );
        $cdn_subscriber = new CDNSubscriber( $options, new CDN( $options ) );

        $hosts = ['example.org'];
        $expected = [
            'example.org',
            'cdn.example.org',
        ];

        $this->assertSame(
            $expected,
            $cdn_subscriber->get_cdn_hosts( $hosts, 'all' )
        );
    }
}
