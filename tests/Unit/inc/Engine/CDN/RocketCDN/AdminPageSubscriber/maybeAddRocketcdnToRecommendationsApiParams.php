<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use Mockery;
use WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::maybe_add_rocketcdn_to_recommendations_api_params
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::maybe_add_rocketcdn_to_recommendations_api_params
 * @group  RocketCDN
 * @group  RocketCDNAdminPage
 */
class Test_MaybeAddRocketcdnToRecommendationsApiParams extends TestCase {

	/**
	 * @var Mockery\MockInterface|APIClient
	 */
	private $api_client;

	/**
	 * @var Mockery\MockInterface|UserClient
	 */
	private $user_client;

	/**
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * @var AdminPageSubscriber
	 */
	private $subscriber;

	public function set_up(): void {
		parent::set_up();

		$this->constants['ROCKETCDN_VERSION'] = '';

		$this->api_client  = Mockery::mock( APIClient::class );
		$this->user_client = Mockery::mock( UserClient::class );
		$this->user        = Mockery::mock( User::class );
		$this->user->shouldReceive( 'is_reseller_account' )->andReturn( false )->byDefault();

		$this->subscriber = new AdminPageSubscriber(
			$this->api_client,
			$this->user_client,
			$this->user,
			''
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldModifyParamsCorrectly( $config, $expected ): void {
		$this->white_label = $config['white_label'] ?? false;

		if ( $config['is_reseller'] ?? false ) {
			$this->user->shouldReceive( 'is_reseller_account' )->andReturn( true );
		}

		$cdn_in_options = in_array( 'cdn', $config['params']['enabled_options'] ?? [], true );

		if ( $this->white_label || $cdn_in_options ) {
			$subscription_data = [
				'subscription_status' => $config['subscription_status'] ?? '',
				'plan_type'           => $config['plan_type'] ?? '',
			];

			$this->api_client->shouldReceive( 'get_subscription_data' )
				->once()
				->andReturn( $subscription_data );
		} else {
			$this->api_client->shouldNotReceive( 'get_subscription_data' );
		}

		$result = $this->subscriber->maybe_add_rocketcdn_to_recommendations_api_params( $config['params'] );

		$this->assertSame( $expected['params'], $result );
	}
}
