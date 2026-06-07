<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CDN\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\Cache;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\CDN\CDN;
use WP_Rocket\Engine\CDN\Admin\Settings;

/**
 * Test class covering \WP_Rocket\Engine\CDN\Admin\Settings::on_update_add_cdn_type_option
 *
 * @group  CDN
 */
class Test_OnUpdateAddCDNTypeOption extends TestCase {
	private $options;

	private $options_api;
	private $settings;
	private $subscription_controller;

	public function setUp(): void {
		parent::setUp();

		$this->options                 = Mockery::mock( Options_Data::class );
		$this->options_api             = Mockery::mock( Options::class );
		$this->subscription_controller = Mockery::mock( SubscriptionController::class );

		$this->settings = new Settings(
			$this->options,
			$this->options_api,
			$this->subscription_controller,
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

		if ( ! $expected['should_update'] ) {
			$this->subscription_controller->expects()->has_active_subscription()->never();
			$this->options_api->expects()->get()->never();
			$this->options_api->expects()->set()->never();

			$this->settings->on_update_add_cdn_type_option( $config['new_version'], $config['old_version'] );
			return;
		}

		$this->subscription_controller->expects()->has_active_subscription()
			->andReturn( $config['has_active_subscription'] ?? false );

		if ( ! ( $config['has_active_subscription'] ?? false ) ) {
			$this->options
				->expects()
				->get( 'cdn_cnames', [] )
				->andReturn( $config['cdn_cnames'] ?? [] );
		}

		$this->options_api
			->expects()
			->get( 'settings', [] )
			->andReturn( $config['current_options'] );

		$this->options_api
			->expects()
			->set( 'settings', $expected['options'] );

		$this->settings->on_update_add_cdn_type_option( $config['new_version'], $config['old_version'] );
	}
}
