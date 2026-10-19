<?php

namespace WP_Rocket\Tests\Unit\inc\ThirdParty\Hostings\Presslabs;

use Brain\Monkey\Actions;
use Brain\Monkey\Filters;
use WP_Rocket\Tests\Unit\TestCase;
use WP_Rocket\ThirdParty\Hostings\Presslabs;

/**
 * Test class covering \WP_Rocket\ThirdParty\Hostings\Presslabs::remove_partial_purge_hooks
 *
 * @group Presslabs
 * @group ThirdParty
 * @group Hostings
 */
class Test_RemovePartialPurgeHooks extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		if ( ! defined( 'WP_CONTENT_DIR' ) ) {
			define( 'WP_CONTENT_DIR', WP_ROCKET_TESTS_FIXTURES_DIR . '/inc/ThirdParty/Hostings/Presslabs' );
		}
	}

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

		( new Presslabs() )->remove_partial_purge_hooks();
	}
}
