<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\License\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\License\Renewal;
use WP_Rocket\Engine\License\Revoked;
use WP_Rocket\Engine\License\Subscriber;
use WP_Rocket\Engine\License\Upgrade;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\License\Subscriber::maybe_schedule_trial_customer_data_refresh
 *
 * @group License
 */
class Test_MaybeScheduleTrialCustomerDataRefresh extends TestCase {
	private $user;
	private $subscriber;

	protected function setUp(): void {
		parent::setUp();

		$this->user = Mockery::mock( User::class );

		$this->subscriber = new Subscriber(
			Mockery::mock( Upgrade::class ),
			Mockery::mock( Renewal::class ),
			Mockery::mock( Revoked::class ),
			$this->user,
			Mockery::mock( UserClient::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldScheduleAsExpected( $config ) {
		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( $config['can_manage_options'] );

		if ( $config['checks_trial'] ) {
			$this->user->shouldReceive( 'is_trial_customer' )
				->once()
				->andReturn( $config['is_trial_customer'] );
		} else {
			$this->user->shouldNotReceive( 'is_trial_customer' );
		}

		if ( $config['checks_schedule'] ) {
			Functions\expect( 'wp_next_scheduled' )
				->once()
				->with( Subscriber::CRON_REFRESH_TRIAL_CUSTOMER_DATA )
				->andReturn( $config['next_scheduled'] );
		} else {
			Functions\expect( 'wp_next_scheduled' )->never();
		}

		if ( $config['checks_expiration'] ) {
			$this->user->shouldReceive( 'get_license_expiration' )
				->once()
				->andReturn( $config['license_expiration'] );
		} else {
			$this->user->shouldNotReceive( 'get_license_expiration' );
		}

		if ( $config['should_schedule'] ) {
			Functions\expect( 'wp_schedule_single_event' )
				->once()
				->with( $config['expected_target_time'], Subscriber::CRON_REFRESH_TRIAL_CUSTOMER_DATA );
		} else {
			Functions\expect( 'wp_schedule_single_event' )->never();
		}

		$this->subscriber->maybe_schedule_trial_customer_data_refresh();
	}
}
