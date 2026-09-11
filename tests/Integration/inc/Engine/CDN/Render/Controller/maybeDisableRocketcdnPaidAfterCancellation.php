<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_disable_rocketcdn_paid_after_cancellation
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_MaybeDisableRocketcdnPaidAfterCancellation extends TestCase {

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var \WP_Rocket\Admin\Options
	 */
	private $options_api;

	public function set_up() {
		parent::set_up();

		$container         = apply_filters( 'rocket_container', null );
		$this->user        = $container->get( 'user' );
		$this->options_api = $container->get( 'options_api' );

		delete_transient( 'rocketcdn_status' );

		// Isolate admin_init to only the Subscriber method under test, so firing the
		// hook doesn't also run the rest of the admin_init surface (some of which
		// redirects or wp_die()s). Restored via restoreWpHook() in tear_down().
		$this->unregisterAllCallbacksExcept( 'admin_init', 'maybe_disable_rocketcdn_paid_after_cancellation' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'admin_init' );

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		$initial_cdn_state = $config['initial_cdn_state'] ?? Context::ROCKETCDN_PAID_TYPE;

		$this->options_api->set( 'settings', array_merge( $this->options_api->get( 'settings', [] ), [ 'cdn_state' => $initial_cdn_state ] ) );
		update_option( 'rocket_rocketcdn_forced_pause_state', [ 'persistent' => ! empty( $config['forced_off_persistent'] ) ] );

		$this->set_subscription_transient( $config );
		$this->set_user_license( $config );

		do_action( 'admin_init' );

		$persisted_cdn_state = $this->options_api->get( 'settings', [] )['cdn_state'] ?? null;
		$forced_off_tracking = get_option( 'rocket_rocketcdn_forced_pause_state', [] );

		$this->assertSame( $expected['cdn_state'] ?? $initial_cdn_state, $persisted_cdn_state );
		$this->assertSame( $expected['persistent'], $forced_off_tracking['persistent'] );

		if ( Context::CDN_STATE_NOTHING === ( $expected['cdn_state'] ?? null ) ) {
			// Confirm the write actually sticks through CdnStateBridge::resolve_live(): once
			// is_cancelled_outside_grace_period() is true (both write-path scenarios reach it),
			// resolve_live() returns null rather than recomputing from live subscription state,
			// so the persisted value above is read back unchanged instead of being overridden.
			$this->assertSame( Context::CDN_STATE_NOTHING, get_rocket_option( 'cdn_state' ) );
		}
	}

	/**
	 * Sets the rocketcdn_status transient from fixture config.
	 */
	private function set_subscription_transient( array $config ): void {
		if ( ! isset( $config['subscription_status'] ) ) {
			return;
		}

		$data = [
			'subscription_status' => $config['subscription_status'],
			'plan_type'           => $config['plan_type'] ?? 'free',
			'status_code'         => 200,
			'cdn_url'             => $config['cdn_url'] ?? '',
		];

		if ( isset( $config['website_status'] ) ) {
			$data['website_status'] = $config['website_status'];
		}

		set_transient( 'rocketcdn_status', $data, HOUR_IN_SECONDS );
	}

	/**
	 * Configures the User instance with the given license state.
	 */
	private function set_user_license( array $config ): void {
		$licence                            = new \stdClass();
		$licence->is_revoked                = ! empty( $config['license_revoked'] );
		$licence->plugin_updates_ban_reason = $config['ban_reason'] ?? '';

		$user_data                     = new \stdClass();
		$user_data->licence_expiration = ! empty( $config['license_expired'] )
			? time() - DAY_IN_SECONDS
			: time() + YEAR_IN_SECONDS;
		$user_data->licence            = $licence;
		$user_data->is_reseller        = ! empty( $config['is_reseller'] );

		$this->user->set_user( $user_data );
	}
}
