<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Engine\Tracking\Tracking;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_rocketcdn_cta
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_rocketcdn_cta
 * @group  RocketCDN
 */
class Test_DisplayRocketcdnCta extends TestCase {

	/**
	 * @var Mockery\MockInterface|APIClient
	 */
	private $api_client;

	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * @var Mockery\MockInterface|NoticesSubscriber
	 */
	private $subscriber;

	public function set_up(): void {
		parent::set_up();

		Functions\stubTranslationFunctions();

		$this->api_client              = Mockery::mock( APIClient::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );

		$this->subscriber = Mockery::mock(
			NoticesSubscriber::class . '[generate]',
			[
				$this->api_client,
				Mockery::mock( Beacon::class ),
				Mockery::mock( UserClient::class ),
				Mockery::mock( Tracking::class ),
				'',
				Mockery::mock( Options_Data::class ),
				$this->subscription_controller,
				$this->user,
			]
		);
	}

	public function testShouldReturnEarlyWhenResellerAccount(): void {
		Functions\expect( 'apply_filters' )
			->once()
			->with( 'rocket_display_rocketcdn_cta', true )
			->andReturn( true );

		$this->white_label = false;

		$this->user->shouldReceive( 'is_reseller_account' )->once()->andReturn( true );

		$this->api_client->shouldNotReceive( 'get_subscription_data' );
		$this->subscription_controller->shouldNotReceive( 'has_active_subscription' );
		$this->subscriber->shouldNotReceive( 'generate' );

		$this->subscriber->display_rocketcdn_cta( [ 'limit_reached' => true ] );
	}
}
