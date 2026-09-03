<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Mockery;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::should_reject_rocketcdn_activation
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::should_reject_rocketcdn_activation
 * @group  CDN
 * @group  RocketCDN
 */
class Test_ShouldRejectRocketcdnActivation extends TestCase {

	/**
	 * Beacon mock instance.
	 *
	 * @var Mockery\MockInterface|Beacon
	 */
	private $beacon;

	/**
	 * CDN Context mock instance.
	 *
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

	/**
	 * Options_Data mock instance.
	 *
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * RocketCDNQuery mock instance.
	 *
	 * @var \PHPUnit\Framework\MockObject\MockObject|RocketCDNQuery
	 */
	private $cdn_query;

	/**
	 * SubscriptionController mock instance.
	 *
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * User mock instance.
	 *
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * Cache mock instance.
	 *
	 * @var Mockery\MockInterface|Cache
	 */
	private $cache;

	/**
	 * Sets up the test fixture.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		$this->beacon                  = Mockery::mock( Beacon::class );
		$this->context                 = Mockery::mock( Context::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->cdn_query               = $this->createMock( RocketCDNQuery::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->user                    = Mockery::mock( User::class );
		$this->cache                   = Mockery::mock( Cache::class );
	}

	/**
	 * Creates a Controller instance under test.
	 *
	 * @return Controller
	 */
	private function get_controller(): Controller {
		return new Controller(
			$this->beacon,
			'',
			$this->context,
			$this->options,
			$this->cdn_query,
			$this->subscription_controller,
			$this->user,
			$this->cache
		);
	}

	/**
	 * Tests that should_reject_rocketcdn_activation returns the expected value.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected value.
	 *
	 * @return void
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		$this->subscription_controller->shouldReceive( 'is_subscription_creation_loading' )
			->andReturn( $config['is_subscription_loading'] );

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( $config['is_rocketcdn'] ?? true );

		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( $config['is_free'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( $config['is_license_invalid'] ?? false );

		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( $config['is_reseller_license_banned'] ?? false );

		$controller = $this->get_controller();

		$this->assertSame( $expected, $controller->should_reject_rocketcdn_activation() );
	}

	/**
	 * Data provider for testShouldDoAsExpected.
	 *
	 * @return array
	 */
	public function configTestData(): array {
		return [
			'rejects while subscription creation is loading' => [
				[
					'is_subscription_loading' => true,
				],
				true,
			],
			'rejects for an expired free-tier licence' => [
				[
					'is_subscription_loading' => false,
					'is_rocketcdn'            => true,
					'is_free'                 => true,
					'is_license_invalid'      => true,
				],
				true,
			],
			'rejects for a revoked free-tier licence'  => [
				[
					'is_subscription_loading' => false,
					'is_rocketcdn'            => true,
					'is_free'                 => true,
					'is_license_invalid'      => true,
				],
				true,
			],
			'rejects for a reseller-banned licence'    => [
				[
					'is_subscription_loading'    => false,
					'is_rocketcdn'               => true,
					'is_free'                    => true,
					// A ban implies is_revoked(), which is_license_invalid() already
					// surfaces - but the ban term must independently reject too, so
					// this asserts the OR branch itself, not just its overlap.
					'is_license_invalid'         => false,
					'is_reseller_license_banned' => true,
				],
				true,
			],
			'allows a healthy free user'               => [
				[
					'is_subscription_loading' => false,
					'is_rocketcdn'            => true,
					'is_free'                 => true,
					'is_license_invalid'      => false,
				],
				false,
			],
		];
	}
}
