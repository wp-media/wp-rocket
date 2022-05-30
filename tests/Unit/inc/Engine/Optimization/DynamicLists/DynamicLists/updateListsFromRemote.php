<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DynamicLists;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DynamicLists\APIClient;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;
use WP_Rocket\Engine\Optimization\DynamicLists\DynamicLists;
use WP_Rocket\Tests\Unit\TestCase;

use function Brain\Monkey\Functions\stubTranslationFunctions;

/**
 * @covers \WP_Rocket\Engine\Optimization\DynamicLists::update_lists_from_remote
 *
 * @group  DynamicLists
 */
class Test_UpdateListsFromRemote extends TestCase {
	protected function setUp(): void {
		parent::setUp();

		$this->stubTranslationFunctions();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $exclusions_list_result ) {
		$api_client = Mockery::mock( APIClient::class );
		$data_manager = Mockery::mock( DataManager::class );
		$dynamic_lists = new DynamicLists( $api_client, $data_manager, '' );

		$hash = '';

		$data_manager
			->shouldReceive( 'get_lists_hash' )
			->once()
			->andReturn( $hash );

		$api_client
			->shouldReceive( 'get_exclusions_list' )
			->with( $hash )
			->once()
			->andReturn( $exclusions_list_result );

		if ( $exclusions_list_result['code'] == 200 ) {
			$data_manager
				->shouldReceive( 'save_dynamic_lists' )
				->with( $exclusions_list_result['body'] )
				->once()
				->andReturn( $exclusions_list_result['not_saved'] );
		}

		$dynamic_lists->update_lists_from_remote();
	}
}
