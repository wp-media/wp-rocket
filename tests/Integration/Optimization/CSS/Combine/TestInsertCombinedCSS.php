<?php
namespace WP_Rocket\Tests\Integration\Optimize\CSS\Combine;

use WP_Rocket\Tests\Integration\TestCase;
use WP_Rocket\Optimization\CSS\Combine;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Admin\Options;
use MatthiasMullie\Minify\CSS;

class TestInsertCombinedCSS extends TestCase {
    public function testShouldInsertCombinedCSS() {
        $combine      = new Combine( new Options_Data( (new Options( 'wp_rocket_'))->get( 'settings' ) ), new CSS() );
        $combined_url = 'combined.css';
        $styles = [
            [
                '<link rel="stylesheet" href="style.css" />',
            ],
            [
                '<link rel="stylesheet" href="plugin.css" />',
            ],
        ];

        $original = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Optimization/CSS/original.html');
        $combined = \file_get_contents( WP_ROCKET_PLUGIN_TESTS_ROOT . '/../Fixtures/Optimization/CSS/combined.html');

        $this->assertSame(
            $combined,
            $combine->insert_combined_css( $original, $combined_url, $styles )
        );
    }
}
