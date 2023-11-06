<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_staging_exclusions()
 *
 * @group  DynamicLists
 */
class Test_AddStagingExclusions extends TestCase {

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_staging_list', 'add_staging_exclusions', 10 );
	}

	public function tear_down() {
		delete_transient( 'wpr_dynamic_lists_staging' );

		$this->restoreWpFilter( 'rocket_staging_list' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $original, $list, $expected ) {
		set_transient( 'wpr_dynamic_lists_staging', $list, HOUR_IN_SECONDS );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_staging_list', $original )
		);
	}
}
