<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\FilesystemTestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists::rest_update_response
 *
 * @group  DynamicLists
 */
class Test_restUpdateResponse extends FilesystemTestCase {

	public function setUp(): void {
		parent::setUp();
		Functions\stubTranslationFunctions();
	}

	protected $path_to_test_data = '/inc/Engine/Optimization/DynamicLists/restUpdateResponse.php';

	/**
	 * @dataProvider providerTestData
	 */
	public function testShouldReturnExpected( $exclusions_list_result, $expected ) {
		$WP_REST_Request   = Mockery::mock( \WP_REST_Request::class );
		$dynamic_lists_api = Mockery::mock( APIClient::class );

		$hash = '';
		$dynamic_lists_api
			->shouldReceive( 'get_exclusions_list' )
			->with( $hash )
			->once()
			->andReturn( $exclusions_list_result );
		$dynamic_lists = new DynamicLists( $dynamic_lists_api, Mockery::mock( DataManager::class ) );
		if ( $exclusions_list_result['code'] == 200 ) {
			Functions\expect( 'set_transient' )
				->with( $exclusions_list_result['body'] )->once()->andReturn(true);
		}
		Functions\expect( 'rest_ensure_response' )
			->with( $exclusions_list_result )->once();

		$dynamic_lists->rest_update_response( $WP_REST_Request );
	}
}
