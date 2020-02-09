<?php
namespace WP_Rocket\Tests\Unit\Optimize\CSS\CombineGoogleFonts;

use WPMedia\PHPUnit\Unit\TestCase;
use WP_Rocket\Optimization\CSS\Combine_Google_Fonts;
use Brain\Monkey\Functions;

/**
 * @group Optimize
 */
class TestOptimize extends TestCase {
    public function testShouldCombineGoogleFontsWhenSubset() {
        Functions\when('rocket_extract_url_component')->alias( function($url, $component ) {
            return parse_url( $url, $component );
        });
        Functions\when('wp_parse_args')->alias( function($value) {
            parse_str( $value, $r );

            return $r;
        });

        $combine = new Combine_Google_Fonts();

        $original = \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/GoogleFonts/original-subset.html');
        $combined = \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/GoogleFonts/combined-subset.html');

        $this->assertSame(
            $combined,
            $combine->optimize( $original )
        );
    }

    public function testShouldCombineGoogleFontsWhenNoSubset() {
        Functions\when('rocket_extract_url_component')->alias( function($url, $component ) {
            return parse_url( $url, $component );
        });
        Functions\when('wp_parse_args')->alias( function($value) {
            parse_str( $value, $r );

            return $r;
        });

        $combine = new Combine_Google_Fonts();

        $original = \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/GoogleFonts/original.html');
        $combined = \file_get_contents( WP_ROCKET_TESTS_FIXTURES_DIR . '/Optimization/CSS/GoogleFonts/combined.html');

        $this->assertSame(
            $combined,
            $combine->optimize( $original )
        );
    }
}
