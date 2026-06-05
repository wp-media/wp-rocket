<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_pause_rocketcdn_for_inactive_subscription
 * @group  CDN
 * @group  RocketCDN
 */
class Test_MaybePauseRocketcdnForInactiveSubscription extends TestCase {

	private $context;
	private $subscription_controller;
	private $controller;

	public function set_up(): void {
		parent::set_up();

		$this->context                 = Mockery::mock( Context::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );

		$this->controller = new Controller(
			Mockery::mock( Beacon::class ),
			'',
			$this->context,
			Mockery::mock( Options_Data::class ),
			$this->createMock( RocketCDNQuery::class ),
			$this->subscription_controller,
			Mockery::mock( User::class )
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpectedValue( array $config, $expected ): void {
		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( $config['is_rocketcdn'] );

		if ( $config['is_rocketcdn'] ) {
			$this->subscription_controller->shouldReceive( 'is_free' )
				->andReturn( $config['is_free'] );

			if ( $config['is_free'] ) {
				$this->subscription_controller->shouldReceive( 'is_license_invalid' )
					->andReturn( $config['is_license_invalid'] );
			}

			if ( ! ( $config['is_free'] && $config['is_license_invalid'] ) ) {
				$this->subscription_controller->shouldReceive( 'get_rocketcdn_status' )
					->andReturn( $config['transient'] );

				if ( false !== $config['transient'] ) {
					$this->subscription_controller->shouldReceive( 'has_inactive_subscription' )
						->andReturn( $config['has_inactive_subscription'] );
				}
			}
		}

		$this->assertSame( $expected, $this->controller->maybe_pause_rocketcdn_for_inactive_subscription( $config['cdn'] ) );
	}
}
