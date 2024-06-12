<?php

namespace WP_Rocket\tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_incompatible_plugins_to_deactivate
 *
 * @group DynamicLists
 */
class Test_AddIncompatiblePluginsToDeactivate extends TestCase {
	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_plugins_to_deactivate', 'add_incompatible_plugins_to_deactivate', 10 );
	}

	public function tear_down() {
		delete_transient( 'wpr_dynamic_lists_incompatible_plugins' );

		$this->restoreWpHook( 'rocket_plugins_to_deactivate' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $original, $plugins_list, $expected ) {
		set_transient( 'wpr_dynamic_lists_incompatible_plugins', $plugins_list, HOUR_IN_SECONDS );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_plugins_to_deactivate', $original )
		);
	}
}
