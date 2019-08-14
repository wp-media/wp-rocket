<?php
namespace WP_Rocket\Tests\Unit\CDN;

use WP_Rocket\CDN\CDN;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

class TestRewriteURL extends TestCase {
    /**
     * @dataProvider rewriteURLProvider
     */
    public function testShouldReturnURLWithCDN( $url, $expected ) {
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
            return 'https://' . $url;
        });

        $this->assertSame(
            $expected,
            $cdn->rewrite_url( $url )
        );
    }

    /**
     * @dataProvider excludedURLProvider
     */
    public function testShouldReturnDefaultURL( $url ) {
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
                    'all',
                ],
            ],
        ];

        $options->method('get')->will($this->returnValueMap($map));

        $cdn = new CDN( $options );

        Functions\when('wp_parse_url')->alias(function ($url, $component = -1) {
            return parse_url($url, $component);
        });

        $this->assertSame(
            $url,
            $cdn->rewrite_url( $url )
        );
    }

    public function rewriteURLProvider() {
        return [
            [
                'http://example.org/wp-content/uploads/test.jpg',
                'http://cdn.example.org/wp-content/uploads/test.jpg',
            ],
            [
                'https://example.org/wp-content/themes/twentynineteen/style.css',
                'https://cdn.example.org/wp-content/themes/twentynineteen/style.css',
            ],
            [
                '/wp-includes/jquery.js',
                'https://cdn.example.org/wp-includes/jquery.js',
            ],
            [
                '//example.org/wp-content/uploads/podcast.mp4',
                '//cdn.example.org/wp-content/uploads/podcast.mp4',
            ],
        ];
    }

    public function excludedURLProvider() {
        return [
            [
                'http://example.org/test.php',
            ],
            [
                'http://example.org/',
            ],
        ];
    }
}
