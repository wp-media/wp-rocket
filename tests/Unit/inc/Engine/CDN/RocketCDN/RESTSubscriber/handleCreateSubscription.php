<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\RESTSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\Rest;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::handle_create_subscription
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\RESTSubscriber::handle_create_subscription
 * @group  CDN
 * @group  RocketCDN
 */
class Test_HandleCreateSubscription extends TestCase {

	/**
	 * @var Mockery\MockInterface|CDNOptionsManager
	 */
	private $cdn_options;

	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Mockery\MockInterface|Rest
	 */
	private $rest;

	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	public function set_up(): void {
		parent::set_up();

		$this->cdn_options             = Mockery::mock( CDNOptionsManager::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->rest                    = Mockery::mock( Rest::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
	}

	private function get_subscriber(): RESTSubscriber {
		return new RESTSubscriber(
			$this->cdn_options,
			$this->options,
			$this->rest,
			$this->subscription_controller
		);
	}

	/**
	 * When create_subscription succeeds, rollback must not be called.
	 */
	public function testShouldNotRollbackWhenSubscriptionCreationSucceeds(): void {
		Functions\when( 'is_wp_error' )->justReturn( false );

		$this->subscription_controller->shouldReceive( 'create_subscription' )
			->once()
			->with( true )
			->andReturn( true );

		$this->rest->shouldNotReceive( 'rollback_failed_subscription' );

		$this->get_subscriber()->handle_create_subscription();
	}

	/**
	 * When create_subscription returns false, rollback must be triggered.
	 */
	public function testShouldRollbackWhenSubscriptionCreationReturnsFalse(): void {
		Functions\when( 'is_wp_error' )->justReturn( false );

		$this->subscription_controller->shouldReceive( 'create_subscription' )
			->once()
			->with( true )
			->andReturn( false );

		$this->rest->shouldReceive( 'rollback_failed_subscription' )->once();

		$this->get_subscriber()->handle_create_subscription();
	}

	/**
	 * When create_subscription returns a WP_Error, rollback must be triggered.
	 */
	public function testShouldRollbackWhenSubscriptionCreationReturnsWpError(): void {
		Functions\when( 'is_wp_error' )->justReturn( true );

		$this->subscription_controller->shouldReceive( 'create_subscription' )
			->once()
			->with( true )
			->andReturn( new \WP_Error( 'rocketcdn_account_notcreated', 'API error' ) );

		$this->rest->shouldReceive( 'rollback_failed_subscription' )->once();

		$this->get_subscriber()->handle_create_subscription();
	}
}
