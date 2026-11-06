<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists::rest_update_response
 *
 * @group  DynamicLists
 */
class Test_restUpdateResponse extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $expired, $exclusions_list_result, $expected ) {
		$dynamic_lists_api = Mockery::mock( APIClient::class );
		$data_manager = Mockery::mock( DataManager::class );
		$user = Mockery::mock( User::class );
		$providers = [
			'defaultlists' =>
				(object) [
					'api_client'   => $dynamic_lists_api,
					'data_manager' => $data_manager,
					'title'        => 'Default Lists',
				],
		];
		$dynamic_lists = new DynamicLists( $providers, $user, '', Mockery::mock( Beacon::class ) );

		$hash = '';

		$user->shouldReceive( 'is_license_expired' )
			->once()
			->andReturn( $expired );

		$data_manager
			->shouldReceive( 'get_lists_hash' )
			->atMost()
			->once()
			->andReturn( $hash );

		$dynamic_lists_api
			->shouldReceive( 'get_exclusions_list' )
			->with( $hash )
			->atMost()
			->once()
			->andReturn( $exclusions_list_result );

		if ( $exclusions_list_result['code'] == 200 ) {
			$data_manager
				->shouldReceive( 'save_dynamic_lists' )
				->with( $exclusions_list_result['body'] )
				->once()
				->andReturn( $exclusions_list_result['not_saved'] );
		}

		Functions\expect( 'rest_ensure_response' )
			->with( $expected )->once();

		$dynamic_lists->rest_update_response();
	}
}
