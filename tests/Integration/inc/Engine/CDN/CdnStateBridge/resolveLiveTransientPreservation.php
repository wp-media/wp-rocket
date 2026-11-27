<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\CdnStateBridge;

use WP_Rocket\Admin\Options;
use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Regression lock: resolve_live() must not flush the rocketcdn_status transient.
 *
 * The flush was removed because it caused a live /status API call on every REST
 * mutation (toggle, page add/remove). Subscription cache invalidation is now
 * event-driven (cron, licence check, checkout redirect) rather than eager on
 * every cdn_state read.
 *
 * @covers \WP_Rocket\Engine\CDN\CdnStateBridge::resolve_live
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_ResolveLiveTransientPreservation extends AdminTestCase {
	/**
	 * WP Options API instance.
	 *
	 * @var Options
	 */
	private $options_api;

	/**
	 * Settings present before this test, restored in tear_down.
	 *
	 * @var array
	 */
	private $original_settings;

	public function set_up() {
		parent::set_up();

		$this->options_api       = new Options( 'wp_rocket_' );
		$this->original_settings = $this->options_api->get( 'settings', [] );
	}

	public function tear_down() {
		$this->options_api->set( 'settings', $this->original_settings );
		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocketcdn_user_token' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldNotFlushTransientWhenResolvingCdnStateLive( array $config ): void {
		set_transient( 'rocketcdn_status', $config['subscription'], MINUTE_IN_SECONDS );

		if ( ! empty( $config['token'] ) ) {
			update_option( 'rocketcdn_user_token', $config['token'] );
		}

		$settings = array_merge( $this->options_api->get( 'settings', [] ), $config['stored'] );
		$this->options_api->set( 'settings', $settings );

		get_rocket_option( 'cdn_state' );

		$this->assertNotFalse(
			get_transient( 'rocketcdn_status' ),
			'rocketcdn_status transient must survive resolve_live() — flushing the cache during cdn_state resolution triggers a spurious /status API call on every REST mutation.'
		);
	}
}
