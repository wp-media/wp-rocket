<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\API\UserClient;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\API\UserClient::get_user_data
 *
 * @group License
 */
class GetUserData extends TestCase {
	private $client;
	private $options;

	public function setUp(): void {
		parent::setUp();

		$this->options = Mockery::mock( Options_Data::class );
		$this->client  = new UserClient( $this->options );
	}

	public function testShouldReturnCachedDataWhenAvailable() {
		$expected = (object) [
			'ID'    => 'cached',
			'email' => 'cached@example.org',
		];

		Functions\expect( 'get_transient' )
			->once()
			->with( 'wp_rocket_customer_data' )
			->andReturn( $expected );
		Functions\expect( 'wp_safe_remote_post' )->never();
		Functions\expect( 'set_transient' )->never();

		$this->assertSame( $expected, $this->client->get_user_data() );
	}

	public function testShouldReturnLocalUserDataWithoutRemoteRequest() {
		Functions\expect( 'get_transient' )
			->once()
			->with( 'wp_rocket_customer_data' )
			->andReturn( false );
		Functions\expect( 'wp_safe_remote_post' )->never();
		Functions\expect( 'set_transient' )
			->once()
			->with( 'wp_rocket_customer_data', Mockery::type( 'object' ), DAY_IN_SECONDS );

		$data = $this->client->get_user_data();

		$this->assertSame( 'local-build', $data->ID );
		$this->assertSame( 999, $data->licence_account );
		$this->assertFalse( $data->licence->is_revoked );
		$this->assertGreaterThan( time(), $data->licence_expiration );
	}
}
