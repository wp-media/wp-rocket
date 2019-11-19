<?php
namespace WP_Rocket\Tests\Integration\Subscriber\Addons\CloudflareSubscriber;

use PHPUnit\Framework\TestCase;
use WP_Rocket\Subscriber\Addons\Cloudflare\CloudflareSubscriber;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;

class TestSetVarnishPurgeRequestHost extends TestCase {
    public function testShouldReturnDefaultWhenCloudflareDisabled() {
        update_option(
            'wp_rocket_settings',
            [
                'do_cloudflare' => 0,
            ]
        );

        $cf_subscriber = new CloudflareSubscriber( new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ) );

        $this->assertSame(
            'example.org',
            $cf_subscriber->set_varnish_purge_request_host( 'example.org' )
        );
    }

    public function testShouldReturnDefaultWhenVarnishDisabled() {
        update_option(
            'wp_rocket_settings',
            [
                'do_cloudflare' => 1,
                'varnish_auto_purge' => 0,
            ]
        );

        $cf_subscriber = new CloudflareSubscriber( new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ) );

        $this->assertSame(
            'example.org',
            $cf_subscriber->set_varnish_purge_request_host( 'example.org' )
        );
    }

    public function testShouldReturnCurrentHostWhenVarnishEnabled() {
        update_option(
            'wp_rocket_settings',
            [
                'do_cloudflare' => 1,
                'varnish_auto_purge' => 1,
            ]
        );

        $cf_subscriber = new CloudflareSubscriber( new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ) );

        $this->assertSame(
            'example.org',
            $cf_subscriber->set_varnish_purge_request_host( 'test.local' )
        );
    }

    public function testShouldReturnCurrentHostWhenFilterTrue() {
        update_option(
            'wp_rocket_settings',
            [
                'do_cloudflare' => 1,
                'varnish_auto_purge' => 0,
            ]
        );

        add_filter( 'do_rocket_varnish_http_purge', '__return_true' );

        $cf_subscriber = new CloudflareSubscriber( new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ) );

        $this->assertSame(
            'example.org',
            $cf_subscriber->set_varnish_purge_request_host( 'test.local' )
        );

        remove_filter( 'do_rocket_varnish_http_purge', '__return_true' );
    }
}