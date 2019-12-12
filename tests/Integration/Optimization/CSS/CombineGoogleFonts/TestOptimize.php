<?php
namespace WP_Rocket\Tests\Integration\Optimize\CSS\CombineGoogleFonts;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Optimization\CSS\Combine_Google_Fonts;

class TestOptimize extends TestCase {
    public function testShouldCombineGoogleFontsWhenSubset() {
        $combine = new Combine_Google_Fonts();

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Optimization/CSS/GoogleFonts/original-subset.html');
        $combined = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Optimization/CSS/GoogleFonts/combined-subset.html');

        $this->assertSame(
            $combined,
            $combine->optimize( $original )
        );
    }

    public function testShouldCombineGoogleFontsWhenNoSubset() {
        $combine = new Combine_Google_Fonts();

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Optimization/CSS/GoogleFonts/original.html');
        $combined = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Optimization/CSS/GoogleFonts/combined.html');

        $this->assertSame(
            $combined,
            $combine->optimize( $original )
        );
    }
}
