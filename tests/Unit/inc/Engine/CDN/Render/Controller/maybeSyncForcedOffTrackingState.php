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
 * Test class covering \WP_Rocket\Engine\CDN\Render\Controller::maybe_sync_forced_off_tracking_state
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_sync_forced_off_tracking_state
 * @group  CDN
 * @group  RocketCDN
 */
class Test_MaybeSyncForcedOffTrackingState extends TestCase {

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
		Functions\when( 'current_user_can' )->justReturn( $config['user_can_manage'] ?? true );

		Functions\when( 'get_option' )->justReturn(
			[ 'tracking' => $config['was_forced'] ?? false ]
		);

		$this->context->shouldReceive( 'is_forced_off' )
			->andReturn( $config['is_forced'] ?? false );

		if ( $expected['update_option_called'] ) {
			Functions\expect( 'update_option' )
				->once()
				->with( 'rocket_rocketcdn_forced_pause_state', Mockery::type( 'array' ), false );
		} else {
			Functions\expect( 'update_option' )->never();
		}

		if ( $expected['cache_cleared'] ) {
			$this->cache->shouldReceive( 'clear_all_cache' )->once();
		} else {
			$this->cache->shouldNotReceive( 'clear_all_cache' );
		}

		if ( null === $expected['event'] ) {
			Actions\expectDone( 'rocket_mixpanel_track_event' )->never();
		} else {
			Actions\expectDone( 'rocket_mixpanel_track_event' )
				->once()
				->with( $expected['event'], $expected['event_data'] );
		}

		if ( array_key_exists( 'reason', $config ) ) {
			$this->context->shouldReceive( 'get_forced_off_reason' )->andReturn( $config['reason'] );
		}

		if ( $expected['update_option_called'] ) {
			$this->options_api->shouldReceive( 'get' )
				->with( 'settings', [] )
				->andReturn( [ 'cdn_state' => $config['settings_cdn_state'] ?? ( $config['cdn_state'] ?? null ) ] );
		}

		if ( array_key_exists( 'cdn_state', $config ) ) {
			$this->context->shouldReceive( 'get_cdn_state' )
				->andReturn( $config['cdn_state'] );
		}

		if ( array_key_exists( 'cdn_status', $config ) ) {
			$this->context->shouldReceive( 'get_cdn_status' )
				->andReturn( $config['cdn_status'] );
		}

		if ( array_key_exists( 'is_paid', $config ) ) {
			$this->subscription_controller->shouldReceive( 'is_paid' )
				->andReturn( $config['is_paid'] );
		}

		$screen     = Mockery::mock( \WP_Screen::class );
		$screen->id = $config['screen_id'] ?? 'settings_page_wprocket';

		$this->get_controller()->maybe_sync_forced_off_tracking_state( $screen );
	}
}
