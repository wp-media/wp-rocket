<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Tracking\Tracking;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Tracking\Tracking::track_option_change() with dynamic lists integration
 *
 * @group  Tracking
 * @group  DynamicLists
 */
class Test_TrackOptionChange extends TestCase {

	public function set_up() {
		parent::set_up();
	}

	public function tear_down() {
		delete_transient( 'wpr_dynamic_lists' );
		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldApplyDynamicMixpanelTrackedOptions( $config, $expected ) {
		// Set up the dynamic list
		set_transient( 'wpr_dynamic_lists', $config['list_data'], HOUR_IN_SECONDS );

		// Apply the filter and check the result
		$actual_options = apply_filters( 'rocket_mixpanel_tracked_options', $config['original_options'] );

		$this->assertSame( $expected, $actual_options );
	}
}
