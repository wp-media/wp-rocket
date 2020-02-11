<?php
namespace WP_Rocket\Tests\Unit\CDN;

use WP_Rocket\CDN\CDN;
use WPMedia\PHPUnit\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @group CDN
 */
class TestRewriteCSSProperties extends TestCase {
    public function testShouldRewriteCSSProperties() {
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
        Functions\when('get_option')->justReturn('http://example.org');
        Functions\when('rocket_add_url_protocol')->alias(function($url) {
            return 'http://' . $url;
        });

        $original = \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/original.css');
        $rewrite  = \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/CDN/rewrite.css');

        $this->assertSame(
            $rewrite,
            $cdn->rewrite_css_properties( $original )
        );
    }
}
