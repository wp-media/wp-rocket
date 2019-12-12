<?php
namespace WP_Rocket\Tests\Integration\Subscriber\CDNSubscriber;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use WP_Rocket\CDN\CDN;
use WP_Rocket\Subscriber\CDN\CDNSubscriber;

/**
 * @group Subscriber
 */
class TestRewriteSrcset extends TestCase {
    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testShouldRewriteSrcsetURLs() {
        update_option(
            'wp_rocket_settings',
            [
                'cdn' => '1',
                'cdn_cnames' => [
                    'cdn.example.org'
                ],
                'cdn_zone' => [
                    'images'
                ],
                'cdn_reject_files' => [],
            ]
        );

        $options        = new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) );
        $cdn_subscriber = new CDNSubscriber( $options, new CDN( $options ) );

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/original.html');
        $rewrite = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/srcset/rewrite.html');

        $this->assertSame(
            $rewrite,
            $cdn_subscriber->rewrite_srcset( $original )
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testShouldReturnOriginalWhenCDNDisabled() {
        update_option(
            'wp_rocket_settings',
            [
                'cdn' => '0',
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

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/original.html');

        $this->assertSame(
            $original,
            $cdn_subscriber->rewrite_srcset( $original )
        );
    }

    /**
     * @runInSeparateProcess
     * @preserveGlobalState disabled
     */
    public function testShouldReturnOriginalWhenNoCNAME() {
        update_option(
            'wp_rocket_settings',
            [
                'cdn' => '1',
                'cdn_cnames' => [
                ],
                'cdn_zone' => [
                ],
                'cdn_reject_files' => [],
            ]
        );

        $options        = new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) );
        $cdn_subscriber = new CDNSubscriber( $options, new CDN( $options ) );

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/original.html');

        $this->assertSame(
            $original,
            $cdn_subscriber->rewrite_srcset( $original )
        );
    }
}
