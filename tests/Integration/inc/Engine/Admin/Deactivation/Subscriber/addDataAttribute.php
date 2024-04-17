<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\Admin\Deactivation\DeactivationIntent;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\Admin\Deactivation\DeactivationIntent::add_data_attribute
 *
 * @group  AdminOnly
 * @group  DeactivationIntent
 */
class Test_AddDataAttribute extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $actions, $expected ) {
		$this->assertSame(
			$expected,
			apply_filters( 'plugin_action_links_wp-rocket/wp-rocket.php', $actions )
		);
	}
}
