<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\UserClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\UserClient::is_licence_rejected
 *
 * @covers \WP_Rocket\Engine\License\API\UserClient::is_licence_rejected
 * @group  License
 */
class Test_IsLicenceRejected extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $transient, $expected ) {
		Functions\expect( 'get_transient' )
			->once()
			->with( UserClient::LICENCE_REJECTED_TRANSIENT )
			->andReturn( $transient );

		$client = new UserClient( Mockery::mock( Options_Data::class ) );

		$this->assertSame( $expected, $client->is_licence_rejected() );
	}
}
