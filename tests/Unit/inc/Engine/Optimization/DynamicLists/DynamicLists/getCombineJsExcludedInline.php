<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Mockery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\DynamicLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::get_combine_js_excluded_inline
 *
 * @group  DynamicLists
 */
class Test_GetCombineJsExcludedInline extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $list, $expected ) {
		$data_manager  = Mockery::mock( DataManager::class );
		$dynamic_lists = new DynamicLists( Mockery::mock( APIClient::class ), $data_manager, Mockery::mock( User::class ), '', Mockery::mock( Beacon::class ) );

		$data_manager->shouldReceive( 'get_lists' )
			->andReturn( $list );

		$this->assertSame(
			$expected,
			$dynamic_lists->get_combine_js_excluded_inline()
		);
	}
}
