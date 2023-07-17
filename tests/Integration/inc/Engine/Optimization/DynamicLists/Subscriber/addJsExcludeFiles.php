<?php

namespace WP_Rocket\tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_js_exclude_files()
 *
 * @group  DynamicLists
 */
class Test_AddJsExcludeFiles extends TestCase {

	public function set_up() {
		parent::set_up();

		$this->unregisterAllCallbacksExcept( 'rocket_exclude_js', 'add_js_exclude_files', 10 );
	}

	public function tear_down() {
		delete_transient( 'wpr_dynamic_lists' );

		$this->restoreWpFilter( 'rocket_exclude_js' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $original, $list, $expected ) {
		set_transient( 'wpr_dynamic_lists', $list, HOUR_IN_SECONDS );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_exclude_js', $original )
		);
	}
}
