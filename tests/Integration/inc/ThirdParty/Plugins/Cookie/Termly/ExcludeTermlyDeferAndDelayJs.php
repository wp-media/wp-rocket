<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\ThirdParty\Plugins\Cookie\Termly;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\ThirdParty\Plugins\Cookie\Termly::exclude_termly_defer_and_delay_js
 *
 * @group Termly
 */
class Test_ExcludeTermlyDeferAndDelayJs extends TestCase {
	/**
	 * Tear down after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		delete_option( 'termly_display_auto_blocker' );

		parent::tear_down();
	}

	/**
	 * Test that the filters return the expected exclusions based on the Termly auto-blocker option.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected values.
	 * @return void
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		update_option( 'termly_display_auto_blocker', $config['termly_display_auto_blocker'] );

		$this->assertSame(
			$expected,
			wpm_apply_filters_typed( 'array', 'rocket_delay_js_exclusions', $config['excluded'] )
		);

		$this->assertSame(
			$expected,
			wpm_apply_filters_typed( 'array', 'rocket_exclude_defer_js', $config['excluded'] )
		);
	}
}
