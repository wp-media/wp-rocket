<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_staging_exclusions()
 *
 * @group  DynamicLists
 */
class Test_AddStagingExclusions extends TestCase {
	protected $dynamic_list;

	public function set_up() {
		parent::set_up();
		$this->unregisterAllCallbacksExcept( 'rocket_staging_list', 'add_staging_exclusions', 10 );
	}

	public function tear_down() {
		remove_filter( 'pre_transient_wpr_dynamic_lists', [$this, 'set_dynamic_list'] );

		$this->restoreWpHook( 'rocket_staging_list' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $original, $list, $expected ) {
		$this->dynamic_list = $list;
		add_filter( 'pre_transient_wpr_dynamic_lists', [$this, 'set_dynamic_list'], 12 );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_staging_list' , $original)
		);
	}

	public function set_dynamic_list() {
		return $this->dynamic_list;
	}
}
