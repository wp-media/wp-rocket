<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\License\API\UserClient;

use Brain\Monkey\Functions;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\License\API\UserClient::get_user_data
 *
 * @group License
 */
class GetUserData extends TestCase {
	public function tearDown() {
		delete_transient( 'wp_rocket_customer_data' );

		parent::tearDown();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $expected ) {
		$client = new UserClient();

		if ( true === $config['transient'] ) {
			set_transient( 'wp_rocket_customer_data', $expected );
		}

		if ( false === $expected ) {
			Functions\expect( 'wp_safe_remote_post' )
			->once()
			->with( UserClient::USER_ENDPOINT )
			->andReturn( $config['response'] );
		}

		$this->assertEquals(
			$expected,
			$client->get_user_data()
		);

		if (
			false === $config['transient']
			&&
			false !== $expected
		) {
			$this->assertEquals(
				$expected,
				get_transient( 'wp_rocket_customer_data' )
			);
		}
	}
}
