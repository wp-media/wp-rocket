<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Subscriber;

use Mockery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Engine\License\Revoked;
use WP_Rocket\Engine\License\Subscriber;
use WP_Rocket\Engine\License\Upgrade;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Subscriber::refresh_trial_customer_data
 *
 * @group License
 */
class Test_RefreshTrialCustomerData extends TestCase {
	public function testShouldFlushAndRefetchCustomerData() {
		$user_client = Mockery::mock( UserClient::class );

		$user_client->shouldReceive( 'flush_cache' )
			->once()
			->ordered();

		$user_client->shouldReceive( 'get_user_data' )
			->once()
			->ordered();

		$subscriber = new Subscriber(
			Mockery::mock( Upgrade::class ),
			Mockery::mock( Renewal::class ),
			Mockery::mock( Revoked::class ),
			Mockery::mock( User::class ),
			$user_client
		);

		$subscriber->refresh_trial_customer_data();
	}
}
