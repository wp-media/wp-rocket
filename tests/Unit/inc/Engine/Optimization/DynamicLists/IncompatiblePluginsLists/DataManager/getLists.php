<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\IncompatiblePluginsLists\DataManager;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\IncompatiblePluginsLists\DataManager as IncDataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\DataManager::get_lists
 *
 * @group DynamicLists
 */
class test_GetLists extends FilesystemTestCase
{
	protected $path_to_test_data = '/inc/Engine/Optimization/DynamicLists/IncompatiblePluginsLists/DataManager/getLists.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected($config, $expected)
	{
		$data_manager = new IncDataManager();
		Functions\when('get_transient')->justReturn($config['data_from_json']);
		Functions\expect('get_rocket_option')
			->andReturnUsing(
				function ($arg) use ($config) {
					return $config['active_options'][$arg];
				});
		$this->assertEquals(
			$expected,
			$data_manager->get_lists()
		);
	}
}
