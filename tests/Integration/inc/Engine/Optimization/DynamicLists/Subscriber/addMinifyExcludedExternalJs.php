<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Optimization\DynamicLists\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\Subscriber::add_minify_excluded_external_js
 *
 * @group  DynamicLists
 */
class Test_AddMinifyExcludedExternalJs extends TestCase {
	public function tear_down() {
		delete_transient( 'wpr_dynamic_lists' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $original, $list, $expected ) {
		set_transient( 'wpr_dynamic_lists', $list, HOUR_IN_SECONDS );

		$this->assertSame(
			$expected,
			apply_filters( 'rocket_minify_excluded_external_js', $original )
		);
	}
}
