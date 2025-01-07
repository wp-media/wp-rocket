<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\DataManager;

use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\DataManager::get_lists_hash
 *
 * @group DynamicLists
 */
class test_GetListsHash extends FilesystemTestCase {
	protected $path_to_test_data = '/inc/Engine/Optimization/DynamicLists/DataManager/getListsHash.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $expected ) {
		$data_manager = new DataManager();

		$this->assertSame(
			$expected,
			$data_manager->get_lists_hash()
		);
	}
}
