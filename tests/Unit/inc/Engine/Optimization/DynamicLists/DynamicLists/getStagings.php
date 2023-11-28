<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Mockery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::get_stagings
 *
 * @group  DynamicLists
 */
class Test_GetStagings extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$data_manager  = Mockery::mock( DataManager::class );
		$data_manager->shouldReceive( 'get_lists' )
			->andReturn( $config['lists'] );
		$providers = [
			'defaultlists' =>
				(object) [
					'api_client' => Mockery::mock( APIClient::class ),
					'data_manager' => $data_manager,
				],
		];
		$dynamic_lists = new DynamicLists( $providers, Mockery::mock( User::class ), '', Mockery::mock( Beacon::class ) );

		$this->assertSame(
			$expected['lists']->staging_domains,
			$dynamic_lists->get_stagings()
		);
	}
}
