<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Mockery;
use ReflectionMethod;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\Render\Controller;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::show_pause_state
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::show_pause_state
 * @group  CDN
 * @group  RocketCDN
 */
class Test_ShowPauseState extends TestCase {

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
	 * Options mock instance.
	 *
	 * @var Mockery\MockInterface|Options
	 */
	private $options_api;

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
		$this->options_api             = Mockery::mock( Options::class );
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
			$this->options_api,
			$this->cdn_query,
			$this->subscription_controller,
			$this->user,
			$this->cache
		);
	}

	/**
	 * Invokes the private show_pause_state() method under test.
	 *
	 * @param Controller $controller  Controller instance.
	 * @param int        $pages_count Number of pages currently tracked for RocketCDN.
	 *
	 * @return bool
	 */
	private function invoke_show_pause_state( Controller $controller, int $pages_count ): bool {
		$show_pause_state = new ReflectionMethod( Controller::class, 'show_pause_state' );
		$show_pause_state->setAccessible( true );

		return $show_pause_state->invoke( $controller, $pages_count );
	}

	/**
	 * Tests that show_pause_state returns the expected value.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param bool  $expected Expected value.
	 *
	 * @return void
	 */
	public function testShouldDoAsExpected( array $config, bool $expected ): void {
		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( $config['applied_cdn_state'] ?? Context::ROCKETCDN_TYPE );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( $config['has_active_subscription'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_in_grace_period' )
			->andReturn( $config['is_in_grace_period'] ?? false );

		$this->context->shouldReceive( 'is_rocketcdn' )
			->andReturn( $config['is_rocketcdn'] ?? true );

		$this->subscription_controller->shouldReceive( 'is_free' )
			->andReturn( $config['is_free'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( $config['is_license_invalid'] ?? false );

		$this->user->shouldReceive( 'is_reseller_license_banned' )
			->andReturn( $config['is_reseller_license_banned'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( $config['is_paid'] ?? false );

		$this->subscription_controller->shouldReceive( 'is_cancelled_outside_grace_period' )
			->andReturn( $config['is_cancelled_outside_grace_period'] ?? false );

		$controller = $this->get_controller();

		$this->assertSame(
			$expected,
			$this->invoke_show_pause_state( $controller, $config['pages_count'] ?? 0 )
		);
	}

	/**
	 * Data provider for testShouldDoAsExpected.
	 *
	 * @return array
	 */
	public function configTestData(): array {
		return [
			'genuinely active: toggle on, nothing else wrong' => [
				[
					'applied_cdn_state' => Context::ROCKETCDN_TYPE,
				],
				false,
			],
			'fresh install: toggle off, never had a subscription' => [
				[
					'applied_cdn_state'       => Context::CDN_STATE_NOTHING,
					'has_active_subscription' => false,
					'is_in_grace_period'      => false,
					'pages_count'             => 0,
				],
				false,
			],
			'subscription deleted outright (outside any grace period): every subscription-state check goes false, but a leftover tracked page proves this wasn\'t a fresh install' => [
				[
					'applied_cdn_state'                 => Context::CDN_STATE_NOTHING,
					'has_active_subscription'           => false,
					'is_in_grace_period'                => false,
					'is_cancelled_outside_grace_period' => false,
					'is_free'                           => false,
					'is_license_invalid'                => false,
					'pages_count'                       => 1,
				],
				true,
			],
			'user deliberately paused an active subscription' => [
				[
					'applied_cdn_state'       => Context::CDN_STATE_NOTHING,
					'has_active_subscription' => true,
				],
				true,
			],
			'cancelled subscription still shows paused while in its grace period, even though has_active_subscription() alone misses it' => [
				[
					'applied_cdn_state'       => Context::CDN_STATE_NOTHING,
					'has_active_subscription' => false,
					'is_in_grace_period'      => true,
				],
				true,
			],
			'toggle genuinely on is never paused merely for being in grace period' => [
				[
					'applied_cdn_state'  => Context::ROCKETCDN_TYPE,
					'is_in_grace_period' => true,
				],
				false,
			],
			'expired free-tier licence' => [
				[
					'applied_cdn_state'  => Context::ROCKETCDN_TYPE,
					'is_rocketcdn'       => true,
					'is_free'            => true,
					'is_license_invalid' => true,
				],
				true,
			],
			'reseller-banned licence'   => [
				[
					'applied_cdn_state'          => Context::ROCKETCDN_TYPE,
					'is_reseller_license_banned' => true,
				],
				true,
			],
			'forced off via is_forced_off(): paid plan cancelled outside its grace period' => [
				[
					'applied_cdn_state'                 => Context::ROCKETCDN_TYPE,
					'is_paid'                           => true,
					'is_cancelled_outside_grace_period' => true,
				],
				true,
			],
		];
	}
}
