<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\IncompatiblePluginsLists\DataManager;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\IncompatiblePluginsLists\DataManager as IncDataManager;
use WP_Rocket\Tests\Unit\FilesystemTestCase;
use WP_Rocket\Admin\Options_Data;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\DataManager::get_lists
 *
 * @group DynamicLists
 */
class test_GetPluginsList extends FilesystemTestCase
{
	protected $path_to_test_data = '/inc/Engine/Optimization/DynamicLists/IncompatiblePluginsLists/DataManager/getPluginsList.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected($config, $expected)
	{
		$options = Mockery::mock( Options_Data::class );
		$data_manager = new IncDataManager($options);
		Functions\when('get_transient')->justReturn($config['data_from_json']);

		$options->shouldReceive( 'get' )->andReturnUsing(
			function ($arg) use ($config) {
				return $config['active_options'][$arg];
			});

		$this->assertEquals(
			$expected,
			$data_manager->get_plugins_list()
		);
	}
}
