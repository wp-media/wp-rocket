<?php

namespace WP_Rocket\Tests\Integration\inc\Media\PreloadFonts\Frontend\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Media\PreloadFonts\Frontend\Subscriber::get_external_font_exclusions
 *
 * @group PerformanceHints
 */
class Test_GetExternalFontExclusions extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		set_transient( 'wpr_dynamic_lists', $config['get_lists'] );

		$this->assertSame(
			$expected,
			wpm_apply_filters_typed( 'string[]', 'rocket_external_font_exclusions', [] )
		);
	}
}
