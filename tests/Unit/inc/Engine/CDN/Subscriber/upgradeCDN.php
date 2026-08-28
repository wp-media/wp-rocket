<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\CdnStateBridge;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Subscriber;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Subscriber::on_update_add_cdn_type_option
 *
 * @group  CDN
 */
class Test_UpgradeCDN extends TestCase {
	private $cdn;
	private $options;
	private $options_api;
	private $subscriber;
	private $subscription_controller;
	private $cdn_state_bridge;

	public function setUp(): void {
		parent::setUp();

		$this->cdn                     = Mockery::mock( CDN::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->options_api             = Mockery::mock( Options::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );
		$this->cdn_state_bridge        = Mockery::mock( CdnStateBridge::class );

		$this->subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			$this->options_api,
			$this->subscription_controller,
			Mockery::mock( Cache::class ),
			$this->createMock( RocketCDN::class ),
			$this->cdn_state_bridge
		);
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldSetExpectedCdnType( array $config, array $expected ) {
		Functions\when( 'rocket_get_constant' )
			->alias(
				function ( $constant ) {
					if ( 'WP_ROCKET_SLUG' === $constant ) {
						return 'wp_rocket_settings';
					}
					return null;
				}
			);

		$this->options_api->expects()->get( 'settings', [] )->andReturn( $config['current_options'] );
		$this->options_api->expects()->set( 'settings', $expected['options'] );

		if ( version_compare( $config['old_version'], '3.22', '<' ) ) {
			$has_active = $config['has_active_subscription'] ?? false;

			$this->subscription_controller->expects()->has_active_subscription()->andReturn( $has_active );

			if ( ! $has_active ) {
				$this->options->expects()->get( 'cdn_cnames', [] )->andReturn( $config['cdn_cnames'] ?? [] );
			}
		}

		$this->cdn_state_bridge->shouldReceive( 'legacy_to_state' )
			->once()
			->andReturn( $config['cdn_state_from_bridge'] );

		$this->subscriber->on_update_add_cdn_type_option( $config['new_version'], $config['old_version'] );
	}
}
