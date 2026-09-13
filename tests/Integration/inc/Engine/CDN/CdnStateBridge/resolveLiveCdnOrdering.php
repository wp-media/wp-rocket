<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\CdnStateBridge;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Regression lock for the ordering fragility introduced by #8751:
 * CdnStateBridge::resolve_live_cdn() discards its incoming $value and seeds the
 * whole live 'cdn' resolution chain, so it must run *before*
 * Render\Controller::maybe_pause_cdn_for_inactive_subscription() (both hooked on
 * 'pre_get_rocket_option_cdn') or the pause's `false` is silently discarded.
 *
 * This exercises the real hook chain end-to-end (does not call either callback
 * directly), so it fails if the bridge's priority is ever reverted to the shared
 * default of 10 *and* inc/Plugin.php's subscriber registration order (which today
 * is the only thing saving that shared-priority case) is swapped.
 *
 * @covers \WP_Rocket\Engine\CDN\CdnStateBridge::resolve_live_cdn
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_ResolveLiveCdnOrdering extends AdminTestCase {
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

	/**
	 * License User instance, shared/singleton on the container.
	 *
	 * @var User
	 */
	private $user;

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Explicitly pin admin context rather than depending on ambient screen state -
		// Subscriber::apply_pause_on_rocketcdn_only() (also hooked on this same 'cdn'
		// read path) behaves differently in admin vs. front end.
		set_current_screen( 'settings_page_wprocket' );

		$container = apply_filters( 'rocket_container', null );

		$this->options_api       = new Options( 'wp_rocket_' );
		$this->original_settings = $this->options_api->get( 'settings', [] );
		$this->user              = $container->get( 'user' );

		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
	}

	/**
	 * Restore state changed by the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		$this->options_api->set( 'settings', $this->original_settings );
		delete_transient( 'rocketcdn_status' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
		remove_all_filters( 'pre_get_rocket_option_cdn' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );
		remove_all_filters( 'get_rocket_option_cdn' );
		$this->user->set_user( new \stdClass() );
		set_current_screen( 'front' );

		parent::tear_down();
	}

	/**
	 * Configures an active free RocketCDN subscription with an expired WP Rocket
	 * licence - the exact condition Render\Controller::is_forced_paused() checks
	 * to force 'cdn' to false.
	 *
	 * @return void
	 */
	private function force_free_licence_expired(): void {
		set_transient(
			'rocketcdn_status',
			[
				'subscription_status' => 'running',
				'plan_type'           => 'free',
				'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			],
			HOUR_IN_SECONDS
		);

		$licence                            = new \stdClass();
		$licence->is_revoked                = false;
		$licence->plugin_updates_ban_reason = '';

		$user_data                     = new \stdClass();
		$user_data->licence_expiration = time() - DAY_IN_SECONDS;
		$user_data->licence            = $licence;
		$user_data->is_reseller        = false;

		$this->user->set_user( $user_data );
	}

	/**
	 * The seeder must run before the pause: the live 'cdn' = 1 stored value must
	 * still resolve to false once forced-paused, and the pause's return value must
	 * survive rather than being overwritten by a later-running seeder.
	 */
	public function testShouldSeedBeforePauseAndPreserveForcedOffValue(): void {
		$settings             = $this->options_api->get( 'settings', [] );
		$settings['cdn']      = 1;
		$settings['cdn_type'] = 'rocketcdn';
		$this->options_api->set( 'settings', $settings );

		$this->force_free_licence_expired();

		$post_filter_calls = 0;
		add_filter(
			'get_rocket_option_cdn',
			function ( $value ) use ( &$post_filter_calls ) {
				++$post_filter_calls;
				return $value;
			},
			20
		);

		$this->assertFalse( get_rocket_option( 'cdn' ), 'The pause must win over the live stored value of 1.' );
		$this->assertSame(
			1,
			$post_filter_calls,
			"apply_pause_on_rocketcdn_only()'s get_rocket_option_cdn post-filter must still fire exactly once per read - re-applied explicitly inside resolve_live_cdn(), not a second time by Options_Data::get()."
		);

		// The whole point of the story: nothing was written to storage by the read.
		$stored = $this->options_api->get( 'settings', [] );
		$this->assertSame( 1, $stored['cdn'] );
		$this->assertSame( 'rocketcdn', $stored['cdn_type'] );
	}

	/**
	 * Same chain on the front end, where apply_pause_on_rocketcdn_only() takes its
	 * non-admin branch - must not undo the pause.
	 */
	public function testShouldPreserveForcedOffValueOnFrontEnd(): void {
		$settings             = $this->options_api->get( 'settings', [] );
		$settings['cdn']      = 1;
		$settings['cdn_type'] = 'rocketcdn';
		$this->options_api->set( 'settings', $settings );

		$this->force_free_licence_expired();

		set_current_screen( 'front' );

		$this->assertFalse( get_rocket_option( 'cdn' ) );
	}
}
