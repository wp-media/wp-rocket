<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Mockery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\DynamicLists\IncompatiblePluginsLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\IncompatiblePluginsLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::getIncompatiblePlugins
 *
 * @group  DynamicLists
 */
class Test_GetIncompatiblePlugins extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $list, $expected ) {
		$data_manager  = Mockery::mock( DataManager::class );
		$providers = [
			'incompatible_plugins' =>
				(object) [
					'api_client' => Mockery::mock( APIClient::class ),
					'data_manager' => $data_manager,
				],
		];
		$dynamic_lists = new DynamicLists( $providers, Mockery::mock( User::class ), '', Mockery::mock( Beacon::class ) );

		$data_manager->shouldReceive( 'get_plugins_list' )
			->andReturn( $list );

		$this->assertSame(
			$expected,
			$dynamic_lists->get_incompatible_plugins()
		);
	}
}
