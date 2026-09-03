<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_turn_off_rocketcdn_for_inactive_subscription
 * @group  CDN
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_MaybeTurnOffRocketcdnForInactiveSubscription extends TestCase {

	/**
	 * @var User
	 */
	private $user;

	/**
	 * @var \WP_Rocket\Admin\Options
	 */
	private $options_api;

	/**
	 * Per-test cdn_type filter closure, removed in tear_down().
	 *
	 * @var callable|null
	 */
	private $cdn_type_filter_callback = null;

	/**
	 * Per-test cdn filter closure, removed in tear_down().
	 *
	 * @var callable|null
	 */
	private $cdn_filter_callback = null;

	public function set_up() {
		parent::set_up();

		$container         = apply_filters( 'rocket_container', null );
		$this->user        = $container->get( 'user' );
		$this->options_api = $container->get( 'options_api' );

		delete_transient( 'rocketcdn_status' );

		// Pre-seed the tracking option with a 'persistent' key: is_forced_off()'s
		// fallthrough branch reads $stored['persistent'] unconditionally, and a
		// genuinely fresh/never-forced-off install has no such key, which throws
		// under this suite's convertNoticesToExceptions setting.
		update_option( 'rocket_rocketcdn_forced_pause_state', [ 'persistent' => false ] );

		$this->options_api->set( 'settings', array_merge( $this->options_api->get( 'settings', [] ), [ 'cdn_state' => Context::ROCKETCDN_PAID_TYPE ] ) );

		// Isolate admin_init to only the Subscriber method under test, so firing the
		// hook doesn't also run the rest of the admin_init surface (some of which
		// redirects or wp_die()s). Restored via restoreWpHook() in tear_down().
		$this->unregisterAllCallbacksExcept( 'admin_init', 'maybe_turn_off_rocketcdn_for_inactive_subscription' );
	}

	public function tear_down() {
		$this->restoreWpHook( 'admin_init' );

		if ( null !== $this->cdn_type_filter_callback ) {
			remove_filter( 'pre_get_rocket_option_cdn_type', $this->cdn_type_filter_callback );
			$this->cdn_type_filter_callback = null;
		}

		if ( null !== $this->cdn_filter_callback ) {
			remove_filter( 'pre_get_rocket_option_cdn', $this->cdn_filter_callback );
			$this->cdn_filter_callback = null;
		}

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, bool $expected_write ): void {
		$cdn_type = $config['cdn_type'];
		$cdn      = $config['cdn_option'];

		$this->cdn_type_filter_callback = static function () use ( $cdn_type ) {
			return $cdn_type;
		};
		$this->cdn_filter_callback = static function () use ( $cdn ) {
			return $cdn;
		};
		add_filter( 'pre_get_rocket_option_cdn_type', $this->cdn_type_filter_callback );
		add_filter( 'pre_get_rocket_option_cdn', $this->cdn_filter_callback );

		$this->set_subscription_transient( $config );
		$this->set_user_license( $config );

		do_action( 'admin_init' );

		$persisted_cdn_state = $this->options_api->get( 'settings', [] )['cdn_state'] ?? null;

		if ( $expected_write ) {
			$this->assertSame( Context::CDN_STATE_NOTHING, $persisted_cdn_state );
		} else {
			// set_up() seeds 'cdn_state' => ROCKETCDN_PAID_TYPE; unchanged means no write occurred.
			$this->assertSame( Context::ROCKETCDN_PAID_TYPE, $persisted_cdn_state );
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
		$licence->plugin_updates_ban_reason  = $config['ban_reason'] ?? '';

		$user_data                     = new \stdClass();
		$user_data->licence_expiration = ! empty( $config['license_expired'] )
			? time() - DAY_IN_SECONDS
			: time() + YEAR_IN_SECONDS;
		$user_data->licence            = $licence;
		$user_data->is_reseller        = ! empty( $config['is_reseller'] );

		$this->user->set_user( $user_data );
	}
}
