<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\DataManager;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\DataManager::save_dynamic_lists
 *
 * @group DynamicLists
 */
class test_SaveDynamicLists extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/DynamicLists/DataManager/saveDynamicLists.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $content, $expected ) {
		$data_manager = new DataManager();

		Functions\expect( 'set_transient' )
			->once()
			->with( 'wpr_dynamic_lists', Mockery::type( 'object' ), WEEK_IN_SECONDS );

		$this->assertSame(
			$expected,
			$data_manager->save_dynamic_lists( $content )
		);
	}
}
