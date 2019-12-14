<?php
namespace WP_Rocket\Tests\Unit\CDN;

use WP_Rocket\CDN\CDN;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @group CDN
 */
class TestRewriteSrcset extends TestCase {
    public function testShouldRewriteURLToCDN() {
        $options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'cdn',
                '',
                1,
            ],
            [
                'cdn_cnames',
                [],
                [
                    'cdn.example.org',
                ],
            ],
            [
                'cdn_reject_files',
                [],
                [],
            ],
            [
                'cdn_zone',
                [],
                [
                    'all'
                ],
            ],
        ];

        $options->method('get')->will($this->returnValueMap($map));

        $cdn = new CDN( $options );

        Functions\when('wp_parse_url')->alias(function ($url, $component = -1 ) {
            return parse_url($url, $component);
        });
        Functions\when('content_url')->justReturn('http://example.org/wp-content/');
        Functions\when('includes_url')->justReturn('http://example.org/wp-includes/');
        Functions\when('wp_upload_dir')->justReturn('http://example.org/wp-content/uploads/');
        Functions\when('get_option')->justReturn('http://example.org');
        Functions\when('rocket_add_url_protocol')->alias(function($url) {
            return 'http://' . $url;
        });

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/original.html');
        $rewrite  = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/srcset/rewrite.html');

        $this->assertSame(
            $rewrite,
            $cdn->rewrite_srcset( $original )
        );
    }

    public function testShouldRewriteURLToCDNWhenZoneIsImages() {
        $options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $map     = [
            [
                'cdn',
                '',
                1,
            ],
            [
                'cdn_cnames',
                [],
                [
                    'cdn.example.org',
                ],
            ],
            [
                'cdn_reject_files',
                [],
                [],
            ],
            [
                'cdn_zone',
                [],
                [
                    'images'
                ],
            ],
        ];

        $options->method('get')->will($this->returnValueMap($map));

        $cdn = new CDN( $options );

        Functions\when('wp_parse_url')->alias(function ($url, $component = -1 ) {
            return parse_url($url, $component);
        });
        Functions\when('content_url')->justReturn('http://example.org/wp-content/');
        Functions\when('includes_url')->justReturn('http://example.org/wp-includes/');
        Functions\when('wp_upload_dir')->justReturn('http://example.org/wp-content/uploads/');
        Functions\when('get_option')->justReturn('http://example.org');
        Functions\when('rocket_add_url_protocol')->alias(function($url) {
            return 'http://' . $url;
        });

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/original.html');
        $rewrite  = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/CDN/srcset/rewrite.html');

        $this->assertSame(
            $rewrite,
            $cdn->rewrite_srcset( $original )
        );
    }
}
