<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_preload_exclusions()
 *
 * @group  DynamicLists
 */
class Test_AddPreloadExclusions extends TestCase {

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_preload_exclude_urls', 'add_preload_exclusions', 10 );
	}

	public function tear_down() {
		delete_transient( 'wpr_dynamic_lists' );

		$this->restoreWpHook( 'rocket_preload_exclude_urls' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $original, $list, $expected ) {
		set_transient( 'wpr_dynamic_lists', $list, HOUR_IN_SECONDS );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_preload_exclude_urls', $original )
		);
	}
}
