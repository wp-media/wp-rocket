<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Flywheel;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Flywheel;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Flywheel::remove_partial_purge_hooks
 *
 * @group Flywheel
 * @group ThirdParty
 * @group Hostings
 */
class Test_RemovePartialPurgeHooks extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRemovePartialPurgeHooks( $expected ) {
		foreach ( $expected['actions'] as $action ) {
			Actions\expectRemoved( $action['action'] )->with( $action['callback'] );
		}
		foreach ( $expected['filters'] as $filter ) {
			Filters\expectRemoved( $filter['filter'] )->with( $filter['callback'] );
		}

		( new Flywheel() )->remove_partial_purge_hooks();
	}
}
