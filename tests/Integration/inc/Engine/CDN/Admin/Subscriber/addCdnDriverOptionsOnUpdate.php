<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Admin\Subscriber;

use WP_Rocket\Tests\Integration\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Admin\Subscriber::add_cdn_driver_options_on_update
 * @group  CDN
 * @group AdminOnly
 */
class Test_AddCdnDriverOptionsOnUpdate extends TestCase {

	private $hook_name = 'wp_rocket_upgrade';
	private $options_api;
	protected $config;

	public function set_up() {
		parent::set_up();

		$container         = apply_filters( 'rocket_container', null );
		$this->options_api = $container->get( 'options_api' );

		$this->unregisterAllCallbacksExcept( $this->hook_name, 'add_cdn_driver_options_on_update', 10 );
	}

	public function tear_down() {
		$settings = $this->options_api->get( 'settings', [] );
		unset( $settings['byocdn'], $settings['rocketcdn'] );
		$this->options_api->set( 'settings', $settings );

		remove_filter( 'pre_transient_rocketcdn_status', [ $this, 'setActiveSubscriptionStatus' ] );

		$this->restoreWpHook( $this->hook_name );
		parent::tear_down();
	}

	public function setActiveSubscriptionStatus(): array {
		return [ 'subscription_status' => 'running' ];
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ) {
		$settings_update = [];

		if ( array_key_exists( 'cdn_enabled', $config ) ) {
			$settings_update['cdn'] = (int) $config['cdn_enabled'];
		}

		if ( ! empty( $config['cdn_cnames'] ) ) {
			$settings_update['cdn_cnames'] = $config['cdn_cnames'];
		}

		if ( ! empty( $settings_update ) ) {
			$this->mergeExistingSettingsAndUpdate( $settings_update );
		}

		if ( ! empty( $config['has_active_subscription'] ) ) {
			add_filter( 'pre_transient_rocketcdn_status', [ $this, 'setActiveSubscriptionStatus' ] );
		}

		do_action( $this->hook_name, $config['new_version'], $config['old_version'] );

		$settings = $this->options_api->get( 'settings', [] );

		foreach ( $expected as $key => $value ) {
			switch ( $key ) {
				case 'byocdn':
					$this->assertSame( $value, $settings['byocdn'] ?? null );
					break;
				case 'rocketcdn':
					$this->assertSame( $value, $settings['rocketcdn'] ?? null );
					break;
				case 'no_change':
					$this->assertArrayNotHasKey( 'byocdn', $settings );
					$this->assertArrayNotHasKey( 'rocketcdn', $settings );
					break;
			}
		}
	}
}
