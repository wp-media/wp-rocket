<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDNSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\CDN\CDN;
use WP_Rocket\Subscriber\CDN\CDNSubscriber;

class TestRewriteCSSProperties extends TestCase {
    public function testShouldRewriteCSSProperties() {
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

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/original.css');
        $rewrite = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/rewrite.css');

        $this->assertSame(
            $rewrite,
            $cdn_subscriber->rewrite_css_properties( $original )
        );
    }

    public function testShouldReturnOriginalWhenFilterIsFalse() {
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

        add_filter( 'do_rocket_cdn_css_properties', '__return_false' );

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/original.css');

        $this->assertSame(
            $original,
            $cdn_subscriber->rewrite_css_properties( $original )
        );
    }
}
