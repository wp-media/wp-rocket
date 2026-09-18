<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Render\Controller;

use Brain\Monkey\Actions;
use Brain\Monkey\Functions;
use Mockery;
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
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::maybe_disable_rocketcdn_paid_after_cancellation
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_disable_rocketcdn_paid_after_cancellation
 * @group  CDN
 * @group  RocketCDN
 */
class Test_MaybeDisableRocketcdnPaidAfterCancellation extends TestCase {
	/**
	 * @var Mockery\MockInterface|Beacon
	 */
	private $beacon;

	/**
	 * @var Mockery\MockInterface|Context
	 */
	private $context;

	/**
	 * @var Mockery\MockInterface|Options_Data
	 */
	private $options;

	/**
	 * @var Mockery\MockInterface|Options
	 */
	private $options_api;

	/**
	 * @var \PHPUnit\Framework\MockObject\MockObject|RocketCDNQuery
	 */
	private $cdn_query;

	/**
	 * @var Mockery\MockInterface|SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * @var Mockery\MockInterface|User
	 */
	private $user;

	/**
	 * @var Mockery\MockInterface|Cache
	 */
	private $cache;

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
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		Functions\when( 'get_current_screen' )->alias(
			function () use ( $config ) {
				return (object) [ 'id' => $config['screen_id'] ?? 'settings_page_wprocket' ];
			}
		);

		$this->options_api->shouldReceive( 'get' )
			->with( 'settings', [] )
			->andReturn( [ 'cdn_state' => $config['initial_cdn_state'] ?? Context::ROCKETCDN_PAID_TYPE ] );

		$this->context->shouldReceive( 'get_applied_cdn_state' )
			->andReturn( $config['applied_cdn_state'] ?? Context::ROCKETCDN_TYPE );

		$this->subscription_controller->shouldReceive( 'has_active_subscription' )
			->andReturn( $config['has_active_subscription'] ?? false );
		$this->subscription_controller->shouldReceive( 'is_paid' )
			->andReturn( $config['is_paid'] ?? false );
		$this->subscription_controller->shouldReceive( 'is_in_grace_period' )
			->andReturn( $config['is_in_grace_period'] ?? false );
		$this->subscription_controller->shouldReceive( 'is_license_invalid' )
			->andReturn( $config['is_license_invalid'] ?? false );

		Functions\when( 'get_option' )->justReturn(
			[ 'persistent' => $config['forced_off_persistent'] ?? false ]
		);

		if ( $expected['update_option_called'] ) {
			Functions\expect( 'update_option' )->once();
		} else {
			Functions\expect( 'update_option' )->never();
		}

		if ( $expected['settings_saved'] ) {
			$this->options_api->shouldReceive( 'set' )
				->once()
				->with( 'settings', [ 'cdn_state' => Context::CDN_STATE_NOTHING ] );
		} else {
			$this->options_api->shouldNotReceive( 'set' );
		}

		if ( $expected['event_fired'] ) {
			// Regression guard: the tracked cdn_mode/cdn_status must reflect the state
			// just written above (Context::CDN_STATE_NOTHING), not the stale prior mode.
			$this->context->shouldReceive( 'get_cdn_state' )
				->with( Context::CDN_STATE_NOTHING )
				->andReturn( Context::CDN_STATE_NOTHING );
			$this->context->shouldReceive( 'get_cdn_status' )
				->with( Context::CDN_STATE_NOTHING )
				->andReturn( 'inactive' );

			Actions\expectDone( 'rocket_mixpanel_track_event' )
				->once()
				->with(
					'RocketCDN Mode Changed',
					[
						'cdn_mode'   => Context::CDN_STATE_NOTHING,
						'cdn_status' => 'inactive',
						'trigger'    => 'pro_cancellation',
					]
				);
		} else {
			Actions\expectDone( 'rocket_mixpanel_track_event' )->never();
		}

		$this->get_controller()->maybe_disable_rocketcdn_paid_after_cancellation();
	}
}
