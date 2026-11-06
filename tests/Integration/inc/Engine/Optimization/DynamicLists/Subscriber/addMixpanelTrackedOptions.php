<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_mixpanel_tracked_options()
 *
 * @group  DynamicLists
 */
class Test_AddMixpanelTrackedOptions extends TestCase {

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_mixpanel_tracked_options', 'add_mixpanel_tracked_options', 10 );
	}

	public function tear_down() {
		delete_transient( 'wpr_dynamic_lists' );

		$this->restoreWpHook( 'rocket_mixpanel_tracked_options' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		set_transient( 'wpr_dynamic_lists', $config['list'], HOUR_IN_SECONDS );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_mixpanel_tracked_options', $config['original'] )
		);
	}
}
