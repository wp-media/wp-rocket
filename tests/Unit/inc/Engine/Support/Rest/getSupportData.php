<?php
declare(strict_types=1);

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
	private Data $support_data;
	private Rest $rest;

	public function set_up() {
		parent::set_up();

		$this->support_data = Mockery::mock( Data::class );
		$this->rest         = new Rest( $this->support_data, Mockery::mock( Options_Data::class ) );
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$this->support_data->shouldReceive( 'get_support_data' )
			->once()
			->andReturn( $config );

		Functions\expect( 'rest_ensure_response' )
			->once()
			->with( $expected );

		$this->rest->get_support_data();
	}
}
