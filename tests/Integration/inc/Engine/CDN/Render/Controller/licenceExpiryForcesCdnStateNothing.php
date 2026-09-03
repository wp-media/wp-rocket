<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * AC1 regression lock: the live read-filter chain forces RocketCDN off on an
 * invalid WP Rocket licence, on both admin and front end, without ever writing
 * to 'cdn' / 'cdn_type' - proving the path is non-destructive and that the
 * renewal path (AutoResumeOnLicenceRenewal) has nothing to restore.
 *
 * Exercises the real hook chain end-to-end - never calls
 * Controller::maybe_pause_cdn_for_inactive_subscription() or
 * CdnStateBridge::resolve_live()/resolve_live_cdn() directly.
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_pause_cdn_for_inactive_subscription
 * @covers \WP_Rocket\Engine\CDN\CdnStateBridge::resolve_live
 * @covers \WP_Rocket\Engine\CDN\CdnStateBridge::resolve_live_cdn
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_LicenceExpiryForcesCdnStateNothing extends TestCase {
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
	 * CDN Context instance, from the container.
	 *
	 * @var Context
	 */
	private $context;

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

		$container = apply_filters( 'rocket_container', null );

		$this->options_api       = new Options( 'wp_rocket_' );
		$this->original_settings = $this->options_api->get( 'settings', [] );
		$this->context           = $container->get( 'cdn_context' );
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
		remove_all_filters( 'pre_get_rocket_option_cdn_state' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );
		remove_all_filters( 'get_rocket_option_cdn' );
		$this->user->set_user( new \stdClass() );
		set_current_screen( 'front' );

		parent::tear_down();
	}

	/**
	 * Tests that an invalid WP Rocket licence forces cdn_state to nothing, in both
	 * admin and front-end contexts, without writing to storage.
	 *
	 * @dataProvider invalidLicenceProvider
	 *
	 * @param array $license_config Configuration passed to set_user().
	 */
	public function testShouldForceCdnStateNothingInAdminAndFrontEnd( array $license_config ): void {
		$settings             = $this->options_api->get( 'settings', [] );
		$settings['cdn']      = 1;
		$settings['cdn_type'] = 'rocketcdn';
		$this->options_api->set( 'settings', $settings );

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
		$licence->is_revoked                = $license_config['is_revoked'];
		$licence->plugin_updates_ban_reason = $license_config['ban_reason'];

		$user_data                     = new \stdClass();
		$user_data->licence_expiration = $license_config['is_expired'] ? time() - DAY_IN_SECONDS : time() + YEAR_IN_SECONDS;
		$user_data->licence            = $licence;
		$user_data->is_reseller        = $license_config['is_reseller'];

		$this->user->set_user( $user_data );

		// Admin context.
		set_current_screen( 'settings_page_wprocket' );

		$this->assertFalse( get_rocket_option( 'cdn' ), 'cdn must resolve falsy in admin.' );
		$this->assertSame( Context::CDN_STATE_NOTHING, get_rocket_option( 'cdn_state' ), 'cdn_state must resolve to nothing in admin.' );
		$this->assertSame( Context::CDN_STATE_NOTHING, $this->context->get_cdn_state(), 'Context::get_cdn_state() must agree, in admin.' );

		// Front end.
		set_current_screen( 'front' );

		$this->assertFalse( get_rocket_option( 'cdn' ), 'cdn must resolve falsy on the front end.' );
		$this->assertSame( Context::CDN_STATE_NOTHING, get_rocket_option( 'cdn_state' ), 'cdn_state must resolve to nothing on the front end.' );
		$this->assertSame( Context::CDN_STATE_NOTHING, $this->context->get_cdn_state(), 'Context::get_cdn_state() must agree, on the front end.' );

		// The whole point of the story: nothing was written to storage by the reads above.
		$stored = $this->options_api->get( 'settings', [] );
		$this->assertSame( 1, $stored['cdn'], 'The force-off must never write to the cdn option.' );
		$this->assertSame( 'rocketcdn', $stored['cdn_type'], 'The force-off must never write to the cdn_type option.' );
	}

	/**
	 * Invalid-licence scenarios that must all force cdn_state to 'nothing'.
	 *
	 * @return array
	 */
	public function invalidLicenceProvider(): array {
		return [
			'expired licence'         => [
				[
					'is_expired'  => true,
					'is_revoked'  => false,
					'ban_reason'  => '',
					'is_reseller' => false,
				],
			],
			'revoked licence'         => [
				[
					'is_expired'  => false,
					'is_revoked'  => true,
					'ban_reason'  => '',
					'is_reseller' => false,
				],
			],
			'reseller-banned licence' => [
				[
					'is_expired'  => false,
					'is_revoked'  => true,
					'ban_reason'  => 'BANNED_WEBSITE',
					'is_reseller' => true,
				],
			],
		];
	}
}
