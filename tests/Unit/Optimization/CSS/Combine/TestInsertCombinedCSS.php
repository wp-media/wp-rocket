<?php
namespace WP_Rocket\Tests\Unit\Optimize\CSS\Combine;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Optimization\CSS\Combine;
use Brain\Monkey\Functions;

/**
 * @group Optimize
 */
class TestInsertCombinedCSS extends TestCase {
    public function testShouldInsertCombinedCSS() {
        Functions\when('create_rocket_uniqid')->justReturn('1234');
        Functions\when('get_current_blog_id')->justReturn('1');

        define( 'WP_ROCKET_MINIFY_CACHE_PATH', 'wp-content/cache/' );
        define('WP_ROCKET_MINIFY_CACHE_URL', 'http://example.com/wp-content/cache/');

        $options = $this->createMock('WP_Rocket\Admin\Options_Data');
        $minify  = $this->createMock('MatthiasMullie\Minify\CSS');

        $combine = new Combine( $options, $minify );
        $combined_url = 'combined.css';
        $styles = [
            [
                '<link rel="stylesheet" href="style.css" />',
            ],
            [
                '<link rel="stylesheet" href="plugin.css" />',
            ],
        ];

        $original = \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/original.html');
        $combined = \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/combined.html');

        $this->assertSame(
            $combined,
            $combine->insert_combined_css( $original, $combined_url, $styles )
        );
    }
}
