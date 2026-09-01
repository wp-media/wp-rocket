<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\Render\Controller;

use ReflectionProperty;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\CDN\Context;
use WP_Rocket\Engine\CDN\RocketCDN\Database\Queries\RocketCDN as RocketCDNQuery;
use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\License\API\User;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\DBTrait;

/**
 * AC2 regression lock: on WP Rocket licence renewal, admin_init (fired for real,
 * not called directly) drives Render\Controller::maybe_auto_create_rocketcdn_free_subscription()
 * to resolve all three outcomes correctly, with no destructive write ever needed
 * for the common case (RocketCDN's own subscription never stopped running).
 *
 * @covers \WP_Rocket\Engine\CDN\Render\Controller::maybe_auto_create_rocketcdn_free_subscription
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_AutoResumeOnLicenceRenewal extends AdminTestCase {
	use DBTrait;

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
	 * RocketCDN Subscription controller instance, from the container.
	 *
	 * @var SubscriptionController
	 */
	private $subscription_controller;

	/**
	 * RocketCDN pages query instance, from the container.
	 *
	 * @var RocketCDNQuery
	 */
	private $cdn_query;

	/**
	 * Installs the RocketCDN pages table for the whole test class.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installRocketCDNTable();
	}

	/**
	 * Uninstalls the RocketCDN pages table after the whole test class.
	 *
	 * @return void
	 */
	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		parent::tear_down_after_class();
	}

	/**
	 * Sets up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		set_current_screen( 'settings_page_wprocket' );

		$container = apply_filters( 'rocket_container', null );

		$this->options_api       = new Options( 'wp_rocket_' );
		$this->original_settings = $this->options_api->get( 'settings', [] );
		$this->context           = $container->get( 'cdn_context' );
		$this->user              = $container->get( 'user' );
		$this->cdn_query         = $container->get( 'rocketcdn_query' );

		// Read the SubscriptionController off the real, admin_init-wired
		// 'cdn_render_controller' singleton (not a fresh `rocketcdn_subscription_controller`
		// from the container - that key is a factory, and this test needs to assert
		// against, and patch, the exact instance production code will use below).
		$render_controller             = $container->get( 'cdn_render_controller' );
		$this->subscription_controller = $this->get_private_property( $render_controller, 'subscription_controller' );

		self::truncateRocketCDNTable();

		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
		delete_option( 'rocketcdn_user_token' );
	}

	/**
	 * Restores state changed by the test.
	 *
	 * @return void
	 */
	public function tear_down() {
		remove_all_filters( 'pre_http_request' );

		$this->options_api->set( 'settings', $this->original_settings );

		self::truncateRocketCDNTable();

		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
		delete_option( 'rocketcdn_user_token' );

		remove_all_filters( 'pre_get_rocket_option_cdn' );
		remove_all_filters( 'pre_get_rocket_option_cdn_state' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );

		$this->user->set_user( new \stdClass() );
		set_current_screen( 'front' );

		parent::tear_down();
	}

	/**
	 * Sets a running or cancelled RocketCDN subscription transient.
	 *
	 * @param string $status Subscription status.
	 *
	 * @return void
	 */
	private function set_subscription_status( string $status ): void {
		set_transient(
			'rocketcdn_status',
			[
				'subscription_status' => $status,
				'plan_type'           => 'free',
				'cdn_url'             => 'https://test.delivery.rocketcdn.me',
			],
			HOUR_IN_SECONDS
		);
	}

	/**
	 * Sets the licence User with a given expiration state.
	 *
	 * @param bool $is_expired Whether the licence should read as expired.
	 *
	 * @return void
	 */
	private function set_licence_state( bool $is_expired ): void {
		$licence                            = new \stdClass();
		$licence->is_revoked                = false;
		$licence->plugin_updates_ban_reason = '';

		$user_data                     = new \stdClass();
		$user_data->licence_expiration = $is_expired ? time() - DAY_IN_SECONDS : time() + YEAR_IN_SECONDS;
		$user_data->licence            = $licence;
		$user_data->is_reseller        = false;
		$user_data->rocketcdn          = (object) [
			'cdn_free_url' => 'https://rocketcdn.me/api/website/create-free/',
		];

		$this->user->set_user( $user_data );
	}

	/**
	 * Reads a private/protected property off an object via Reflection.
	 *
	 * @param object $target        Object to read from.
	 * @param string $property_name Property name.
	 *
	 * @return mixed
	 */
	private function get_private_property( object $target, string $property_name ) {
		$prop = new ReflectionProperty( $target, $property_name );
		$prop->setAccessible( true );

		return $prop->getValue( $target );
	}

	/**
	 * Sets a private/protected property on an object via Reflection.
	 *
	 * @param object $target        Object to write to.
	 * @param string $property_name Property name.
	 * @param mixed  $value         Value to set.
	 *
	 * @return void
	 */
	private function set_private_property( object $target, string $property_name, $value ): void {
		$prop = new ReflectionProperty( $target, $property_name );
		$prop->setAccessible( true );
		$prop->setValue( $target, $value );
	}

	/**
	 * CreateAPIClient reads User::get_rocketcdn_free_url() once, in its own
	 * constructor, and stores it in a private property it never re-reads live.
	 * The SubscriptionController instance this test asserts against was built
	 * (and its nested CreateAPIClient's URL frozen) before set_licence_state()
	 * ever ran, so the frozen URL must be patched directly for create_subscription()
	 * to be able to make its request at all - otherwise send_request() bails on
	 * "Empty Url." before any HTTP call, regardless of pre_http_request mocking.
	 *
	 * @param string $url URL to force onto the frozen CreateAPIClient instance.
	 *
	 * @return void
	 */
	private function force_create_api_client_free_url( string $url ): void {
		$create_api_client = $this->get_private_property( $this->subscription_controller, 'create_api_client' );
		$this->set_private_property( $create_api_client, 'free_url', $url );
	}

	/**
	 * Mocks a successful 'create-free' API response ('cdn_task_enqueued').
	 *
	 * @return void
	 */
	private function mock_successful_create_free_api(): void {
		add_filter(
			'pre_http_request',
			function ( $preempt, $_args, $url ) {
				if ( false === strpos( $url, 'https://rocketcdn.me/api/website/create-free/' ) ) {
					return $preempt;
				}

				return [
					'response' => [
						'code'    => 200,
						'message' => 'OK',
					],
					'body'     => wp_json_encode(
						[
							'success' => true,
							'data'    => [
								'code'      => 'cdn_task_enqueued',
								'task_id'   => 'task_abc_123',
								'cdn_token' => 'newtoken12345678901234567890123456789',
							],
						]
					),
				];
			},
			10,
			3
		);
	}

	/**
	 * Common baseline: cdn=1 / cdn_type=rocketcdn, and the licence starts expired
	 * so the toggle is force-paused - proving Task 6.1's non-destructive force-off
	 * is what this renewal path resumes from, rather than anything this test sets up.
	 *
	 * @return void
	 */
	private function seed_expired_baseline(): void {
		$settings             = $this->options_api->get( 'settings', [] );
		$settings['cdn']      = 1;
		$settings['cdn_type'] = 'rocketcdn';
		$this->options_api->set( 'settings', $settings );

		$this->set_licence_state( true );

		$this->assertSame( Context::CDN_STATE_NOTHING, $this->context->get_cdn_state(), 'Sanity check: force-off must be active before renewal.' );
	}

	/**
	 * Common case: the WP Rocket licence lapsed but RocketCDN's own free
	 * subscription kept running throughout. On renewal, the pause simply lifts -
	 * no write, no re-creation.
	 */
	public function testShouldResumeWithNoWritesWhenSubscriptionStillRunning(): void {
		$this->seed_expired_baseline();
		$this->set_subscription_status( 'running' );

		$this->set_licence_state( false );

		do_action( 'admin_init' );

		$this->assertSame( Context::ROCKETCDN_FREE_TYPE, $this->context->get_cdn_state(), 'cdn_state must resolve back to rocketcdn_free with no restore code.' );
		$this->assertFalse( get_transient( 'rocket_cdn_subscription_creation_in_progress' ), 'create_subscription() must never be invoked in this scenario.' );

		$stored = $this->options_api->get( 'settings', [] );
		$this->assertSame( 1, $stored['cdn'], 'cdn must never have been written by the force-off or the resume.' );
		$this->assertSame( 'rocketcdn', $stored['cdn_type'] );
	}

	/**
	 * RocketCDN also cancelled the free subscription while the licence was
	 * expired, but free pages are still on record. On renewal, a new
	 * subscription is auto-created and the state reflects "creation in
	 * progress" rather than silently staying off.
	 */
	public function testShouldAutoCreateSubscriptionWhenCancelledWithPagesPresent(): void {
		$this->seed_expired_baseline();
		$this->set_subscription_status( 'cancelled' );

		$this->cdn_query->add_item(
			[
				'url'           => 'http://example.org/',
				'title'         => 'Home',
				'modified'      => current_time( 'mysql' ),
				'last_accessed' => current_time( 'mysql' ),
			]
		);

		$this->mock_successful_create_free_api();

		$this->set_licence_state( false );
		$this->force_create_api_client_free_url( 'https://rocketcdn.me/api/website/create-free/' );

		do_action( 'admin_init' );

		$this->assertNotFalse( get_transient( 'rocket_cdn_subscription_creation_in_progress' ), 'create_subscription() must have been invoked.' );
		$this->assertSame( 'newtoken12345678901234567890123456789', get_option( 'rocketcdn_user_token' ) );
		$this->assertSame( Context::ROCKETCDN_STATE_ONGOING_FREE, $this->context->get_rocketcdn_state(), 'Status indicator must show creation-in-progress, not silently stay off.' );
	}

	/**
	 * RocketCDN also cancelled the free subscription, and no free pages are on
	 * record. There is nothing to re-create - the feature stays disabled until
	 * the user re-enables it manually.
	 */
	public function testShouldStayNothingWhenCancelledWithNoPages(): void {
		$this->seed_expired_baseline();
		$this->set_subscription_status( 'cancelled' );

		$this->mock_successful_create_free_api();

		$this->set_licence_state( false );

		do_action( 'admin_init' );

		$this->assertFalse( get_transient( 'rocket_cdn_subscription_creation_in_progress' ), 'create_subscription() must not be invoked when there are no pages to re-create.' );
		$this->assertSame( '', (string) get_option( 'rocketcdn_user_token', '' ) );
		$this->assertSame( Context::CDN_STATE_NOTHING, $this->context->get_cdn_state() );

		$stored = $this->options_api->get( 'settings', [] );
		$this->assertSame( 1, $stored['cdn'], 'cdn must remain untouched even though the driver stays effectively off.' );
	}
}
