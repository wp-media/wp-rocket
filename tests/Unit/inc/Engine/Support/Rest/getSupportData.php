<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Support\Rest;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Support\Data;
use WP_Rocket\Engine\Support\Rest;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Support\Rest::get_support_data
 *
 * @group Support
 */
class Test_GetSupportData extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $support_data, $expected ) {
		$data = Mockery::mock( Data::class );
		$rest = new Rest( $data, Mockery::mock( Options_Data::class ) );

		$data->shouldReceive( 'get_support_data' )
			->atMost()
			->once()
			->andReturn( $support_data );

		Functions\expect( 'rest_ensure_response' )
			->once()
			->with( $expected );
	
		$rest->get_support_data();
	}
}
