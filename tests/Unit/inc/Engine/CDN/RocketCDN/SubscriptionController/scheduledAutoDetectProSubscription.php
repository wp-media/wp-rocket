<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\SubscriptionController;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CheckStatusAPIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\CreateAPIClient;
use WP_Rocket\Engine\CDN\RocketCDN\APIHandler\WebsiteSearch;
use WP_Rocket\Engine\CDN\RocketCDN\CDNOptionsManager;
use WP_Rocket\Engine\CDN\RocketCDN\Queue;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering the "no RocketCDN token" decision of \WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController::scheduled_auto_detect_pro_subscription
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController::scheduled_auto_detect_pro_subscription
 * @group  CDN
 * @group  RocketCDN
 */
class Test_ScheduledAutoDetectProSubscription extends TestCase {
	private $user_client;
	private $api_client;
	private $queue;
	private $controller;

	protected function setUp(): void {
		parent::setUp();

		$this->user_client = Mockery::mock( UserClient::class );
		$this->api_client  = Mockery::mock( APIClient::class );
		$this->queue       = Mockery::mock( Queue::class );
		$this->controller  = new SubscriptionController(
			$this->api_client,
			Mockery::mock( CreateAPIClient::class ),
			Mockery::mock( CDNOptionsManager::class ),
			$this->queue,
			Mockery::mock( CheckStatusAPIClient::class ),
			Mockery::mock( User::class ),
			Mockery::mock( WebsiteSearch::class ),
			$this->user_client
		);

		$this->user_client->shouldReceive( 'flush_cache' )->once();
	}

	public function testNoTokenOnANormalAnswerIsConclusive() {
		$this->user_client->shouldReceive( 'get_user_data' )->once()->andReturn( (object) [ 'status' => 'active' ] );
		Functions\expect( 'delete_transient' )->once()->with( 'rocket_cdn_pro_detection_failed' );
		$this->queue->shouldReceive( 'cancel_pro_detection_job' )->once();
		$this->api_client->shouldNotReceive( 'get_subscription_data' );

		$this->controller->scheduled_auto_detect_pro_subscription( 2 );
	}

	/**
	 * @dataProvider providerInconclusive
	 */
	public function testShouldRetryWhenTheUserDataIsInconclusive( $user_data ) {
		$this->user_client->shouldReceive( 'get_user_data' )->once()->andReturn( $user_data );
		$this->queue->shouldNotReceive( 'cancel_pro_detection_job' );
		$this->api_client->shouldReceive( 'get_subscription_data' )->once()->andReturn( [ 'status_code' => 500 ] );
		$this->queue->shouldReceive( 'schedule_pro_detection_job' )->once()->with( 1 );

		$this->controller->scheduled_auto_detect_pro_subscription( 2 );
	}

	public function providerInconclusive() {
		return [
			'call failed'           => [ false ],
			'edge 403 error object' => [
				(object) [
					'code'    => 403,
					'detail'  => 'Forbidden',
					'success' => false,
				],
			],
			'JSON 404 error object' => [
				(object) [
					'code'    => 404,
					'detail'  => 'User not found.',
					'success' => false,
				],
			],
		];
	}
}
