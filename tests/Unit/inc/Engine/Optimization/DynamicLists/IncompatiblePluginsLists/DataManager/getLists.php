<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\IncompatiblePluginsLists\DataManager;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\IncompatiblePluginsLists\DataManager as IncDataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\DataManager::get_lists
 *
 * @group DynamicLists
 */
class test_GetLists extends TestCase
{
	protected $path_to_test_data = '/inc/Engine/Optimization/DynamicLists/IncompatiblePluginsLists/DataManager/getLists.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected($config, $expected)
	{
		var_dump('xxxxxxxxxxxxxxxx');
		var_dump($this->filepathx);
		$data_manager = new IncDataManager();
		if ($config['condition'] !== "") {
			Functions\expect('get_rocket_option')
				->once();
		}
		$this->assertEquals(
			$expected,
			$data_manager->get_lists()
		);
	}
}
