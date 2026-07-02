<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\RocketCDN\AdminPageSubscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::display_rocketcdn_status
 *
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\AdminPageSubscriber::display_rocketcdn_status
 * @group  RocketCDN
 * @group  RocketCDNAdminPage
 */
class Test_DisplayRocketcdnStatus extends TestCase {

	/**
	 * @var Mockery\MockInterface|APIClient
	 */
	private $api_client;

	/**
	 * @var Mockery\MockInterface|UserClient
	 */
	private $user_client;

	/**
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

	/**
	 * @var Mockery\MockInterface|AdminPageSubscriber
	 */
	private $subscriber;

	public function set_up(): void {
		parent::set_up();

		Functions\stubTranslationFunctions();

		$this->api_client  = Mockery::mock( APIClient::class );
		$this->user_client = Mockery::mock( UserClient::class );
		$this->context     = Mockery::mock( Context::class );

		$this->subscriber = Mockery::mock(
			AdminPageSubscriber::class . '[generate]',
			[ $this->api_client, $this->user_client, '', $this->context ]
		);
	}

	/**
	 * Fixture format matches the integration test: ($rocketcdn_status, $expected, $config).
	 * Unit assertions use $expected['unit']; integration assertions use $expected['integration'].
	 *
	 * @dataProvider configTestData
	 */
	public function testShouldPassCorrectDataToTemplate( $rocketcdn_status, $expected, $config ): void {
		$this->white_label = $config['white_label'] ?? false;

		if ( $this->white_label ) {
			$this->subscriber->shouldNotReceive( 'generate' );
			$this->subscriber->display_rocketcdn_status();
			return;
		}

		$this->api_client->shouldReceive( 'get_subscription_data' )
			->andReturn( $rocketcdn_status );

		Functions\expect( 'rocket_is_live_site' )
			->andReturn( $config['is_live_site'] );

		$is_paid_running = 'running' === ( $rocketcdn_status['subscription_status'] ?? '' )
			&& 'paid' === ( $rocketcdn_status['plan_type'] ?? '' );

		if ( $is_paid_running ) {
			Functions\expect( 'get_option' )
				->with( 'date_format' )
				->andReturn( $config['get_option'] );

			Functions\expect( 'date_i18n' )
				->andReturn( $config['date_i18n'] );
		}

		$this->subscriber->shouldReceive( 'generate' )
			->once()
			->with( 'dashboard-status', $expected['unit'] )
			->andReturn( '' );

		$this->subscriber->display_rocketcdn_status();
	}
}
