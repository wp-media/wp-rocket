<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\Cache;
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

	public function setUp(): void {
		parent::setUp();

		$this->cdn                     = Mockery::mock( CDN::class );
		$this->options                 = Mockery::mock( Options_Data::class );
		$this->options_api             = Mockery::mock( Options::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );

		$this->subscriber = new Subscriber(
			$this->options,
			$this->cdn,
			$this->options_api,
			$this->subscription_controller,
			Mockery::mock( Cache::class ),
			$this->createMock( RocketCDN::class )
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

		$is_less_than_322 = version_compare( $config['old_version'], '3.22', '<' );

		if ( $is_less_than_322 ) {
			$has_active = $config['has_active_subscription'] ?? false;

			$this->subscription_controller->expects()->has_active_subscription()->andReturn( $has_active );

			if ( ! $has_active ) {
				$this->options->expects()->get( 'cdn_cnames', [] )->andReturn( $config['cdn_cnames'] ?? [] );
			}
		}

		// has_active_subscription() is called by compute_cdn_state_from_legacy() for >= 3.22
		// when cdn=1 and cdn_type='rocketcdn'. Fixture includes the key only when the call is expected.
		if ( ! $is_less_than_322 && array_key_exists( 'has_active_subscription', $config ) ) {
			$this->subscription_controller->expects()->has_active_subscription()
				->andReturn( $config['has_active_subscription'] );
		}

		if ( array_key_exists( 'is_cancelled_outside_grace_period', $config ) ) {
			$this->subscription_controller->expects()->is_cancelled_outside_grace_period()
				->andReturn( $config['is_cancelled_outside_grace_period'] );
		}

		if ( array_key_exists( 'is_paid', $config ) ) {
			$this->subscription_controller->expects()->is_paid()
				->andReturn( $config['is_paid'] );
		}

		$this->subscriber->on_update_add_cdn_type_option( $config['new_version'], $config['old_version'] );
	}
}
