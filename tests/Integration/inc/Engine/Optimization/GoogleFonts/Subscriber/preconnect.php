<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\GoogleFonts\Subscriber;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\GoogleFonts\Subscriber::preconnect
 * @group CombineGoogleFonts
 */
class Test_Preconnect extends TestCase {
    /**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpectedArray( $option_value, $urls, $relation_type, $expected ) {
        $callback = function() use ( $option_value ) {
            return $option_value;
        };

		add_filter( 'pre_get_rocket_option_minify_google_fonts', $callback );

		$this->assertSame(
			$expected,
			apply_filters( 'wp_resource_hints', $urls, $relation_type )
        );

        remove_filter( 'pre_get_rocket_option_minify_google_fonts', $callback );
	}

	public function providerTestData() {
		return $this->getTestData( __DIR__, 'preconnect' );
	}
}