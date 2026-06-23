<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use ReflectionProperty;
use WP_Rocket\Tests\Integration\TestCase;

/**
 * Integration tests covering Section 2 – Paid subscription cancel/refund flows.
 *
 * TC-2.1  Cancel paid immediately + delete website → outside grace period.
 * TC-2.2  Cancel paid + website NOT deleted (pending_deletion / grace period) → paid paused.
 * TC-2.3  Refund (cancel from account + delete from CDN app) → outside grace period.
 * TC-2.4  Re-purchase paid CDN after refund/cancel → active paid subscription.
 * TC-2.5  API failure during free subscription creation → loader transient deleted.
 * TC-2.6  Upgrade: cancel without deleting website → grace period shown.
 * TC-2.7  Upgrade: cancel + delete website → auto-creates free subscription.
 *
 * @group RocketCDN
 * @group CDN
 */
class Test_CancelRefundFlows extends TestCase {

	protected static $use_settings_trait = true;

	protected static $transients = [
		'rocketcdn_status'          => null,
		'rocket_cdn_website_search' => null,
	];

	const CDN_URL = 'https://abcd1234.delivery.rocketcdn.me';
	const TOKEN   = '1234567890123456789012345678901234567890';

	/** @var \WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController */
	private $subscription_controller;

	/** @var \WP_Rocket\Engine\CDN\Context */
	private $context;

	/** @var \WP_Rocket\Engine\CDN\Render\Controller */
	private $render_controller;

	// -------------------------------------------------------------------------
	// Lifecycle
	// -------------------------------------------------------------------------

	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installRocketCDNTable();
	}

	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		parent::tear_down_after_class();
	}

	public function set_up() {
		parent::set_up();

		$container = apply_filters( 'rocket_container', null );

		$this->subscription_controller = $container->get( 'rocketcdn_subscription_controller' );
		$this->context                 = $container->get( 'cdn_context' );
		$this->render_controller       = $container->get( 'cdn_render_controller' );

		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_website_search' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		delete_transient( 'rocket_cdn_create_request' );
		delete_transient( 'rocket_cdn_check_status_request' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
		delete_transient( 'wp_rocket_customer_data' );

		// Reset the FrontendSubscriber's per-request memoized CDN URL so each
		// test starts with a fresh lookup.
		$frontend = $container->get( 'rocketcdn_frontend_subscriber' );
		$prop     = new ReflectionProperty( $frontend, 'rocketcdn_url' );
		$prop->setAccessible( true );
		$prop->setValue( $frontend, null );

		self::truncateRocketCDNTable();

		// Baseline settings: CDN enabled, RocketCDN driver.
		$settings              = get_option( 'wp_rocket_settings', [] );
		$settings['cdn']       = 1;
		$settings['cdn_type']  = 'rocketcdn';
		update_option( 'wp_rocket_settings', $settings );

		update_option( 'rocketcdn_user_token', self::TOKEN );

		set_current_screen( 'settings_page_wprocket' );
		add_filter( 'home_url', [ $this, 'home_url_cb' ] );

		// Keep only the CDN rewrite callback on rocket_buffer to isolate it.
		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'rewrite', 2 );
	}

	public function tear_down() {
		remove_all_filters( 'pre_http_request' );
		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );

		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_website_search' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		delete_transient( 'rocket_cdn_create_request' );
		delete_transient( 'rocket_cdn_check_status_request' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
		delete_transient( 'wp_rocket_customer_data' );

		self::truncateRocketCDNTable();
		set_current_screen( 'front' );

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	public function home_url_cb(): string {
		return 'http://example.org';
	}

	// -------------------------------------------------------------------------
	// TC-2.1 / TC-2.2 / TC-2.3 / TC-2.4 / TC-2.6 – Subscription state tests
	// -------------------------------------------------------------------------

	/**
	 * Verifies that the subscription state, CDN context driver, and CNAME
	 * rewriting all reflect the correct values after cancel / refund API states.
	 *
	 * @dataProvider configTestData
	 */
	public function testShouldReflectSubscriptionStateAfterCancelRefund( array $config, array $expected ): void {
		// --- Optional: pre-set the forced-pause tracking option to skip the
		//     cache-clear side-effect inside maybe_pause_cdn_for_inactive_subscription.
		if ( isset( $config['forced_pause_tracking'] ) ) {
			update_option( 'rocket_rocketcdn_forced_pause_state', $config['forced_pause_tracking'] );
		}

		// --- Populate free-tier page list if the scenario requires pre-existing pages.
		if ( ! empty( $config['free_pages'] ) ) {
			$query = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );
			foreach ( $config['free_pages'] as $page ) {
				$query->add_item(
					[
						'url'           => $page['url'],
						'title'         => $page['title'],
						'modified'      => current_time( 'mysql' ),
						'last_accessed' => current_time( 'mysql' ),
					]
				);
			}
		}

		// --- Wire up API mocks via pre_http_request.
		$this->mock_rocketcdn_api( $config );

		// --- Subscription state assertions.
		if ( array_key_exists( 'has_active_subscription', $expected ) ) {
			$this->assertSame(
				$expected['has_active_subscription'],
				$this->subscription_controller->has_active_subscription(),
				'has_active_subscription'
			);
		}

		if ( array_key_exists( 'is_in_grace_period', $expected ) ) {
			$this->assertSame(
				$expected['is_in_grace_period'],
				$this->subscription_controller->is_in_grace_period(),
				'is_in_grace_period'
			);
		}

		if ( array_key_exists( 'is_cancelled_outside_grace_period', $expected ) ) {
			$this->assertSame(
				$expected['is_cancelled_outside_grace_period'],
				$this->subscription_controller->is_cancelled_outside_grace_period(),
				'is_cancelled_outside_grace_period'
			);
		}

		if ( array_key_exists( 'is_paid', $expected ) ) {
			$this->assertSame(
				$expected['is_paid'],
				$this->subscription_controller->is_paid(),
				'is_paid'
			);
		}

		// --- CDN context driver.
		if ( array_key_exists( 'context_driver', $expected ) ) {
			$this->assertSame(
				$expected['context_driver'],
				$this->context->get_driver(),
				'context driver'
			);
		}

		// --- CNAME rewriting via rocket_buffer (assert .rocketcdn.me present/absent).
		if ( array_key_exists( 'cname_applied', $expected ) ) {
			$html   = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';
			$result = apply_filters( 'rocket_buffer', $html );

			if ( $expected['cname_applied'] ) {
				$this->assertStringContainsString(
					'.rocketcdn.me',
					$result,
					'Expected .rocketcdn.me CNAME to be applied in rocket_buffer output'
				);
			} else {
				$this->assertStringNotContainsString(
					'.rocketcdn.me',
					$result,
					'Expected .rocketcdn.me CNAME NOT to appear in rocket_buffer output'
				);
			}
		}

		// --- Render controller element-disable flag.
		if ( array_key_exists( 'should_disable_element', $expected ) ) {
			$this->assertSame(
				$expected['should_disable_element'],
				$this->render_controller->should_disable_element_for_rocketcdn(),
				'should_disable_element_for_rocketcdn'
			);
		}

		// --- Free-tier page list preserved in DB.
		if ( array_key_exists( 'free_pages_count_in_db', $expected ) ) {
			$query = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );
			$this->assertCount(
				$expected['free_pages_count_in_db'],
				$query->query( [] ),
				'free pages count in DB'
			);
		}
	}

	// -------------------------------------------------------------------------
	// TC-2.5 – API failure during free subscription creation
	// -------------------------------------------------------------------------

	/**
	 * TC-2.5: When the subscription creation API returns a 500 error, the
	 * loader transient must be removed and create_subscription() must return
	 * a WP_Error so that no success path is executed.
	 */
	public function testShouldHandleSubscriptionCreationFailureGracefully(): void {
		// Wire up the customer data so CreateAPIClient has a non-empty free URL.
		set_transient(
			'wp_rocket_customer_data',
			(object) [
				'rocketcdn' => (object) [
					'cdn_free_url' => 'https://rocketcdn.me/api/website/create-free/',
				],
			]
		);

		// Mock the create endpoint to return a 500 error.
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/create-free/' ) ) {
					return new \WP_Error( 'http_request_failed', 'Internal Server Error' );
				}
				return $preempt;
			},
			10,
			3
		);

		// Ensure the subscription controller does not think there is an active subscription.
		// (No rocketcdn_status transient and subscription/status → 404.)
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/subscription/' ) ) {
					return [
						'response' => [ 'code' => 404, 'message' => 'Not Found' ],
						'body'     => '',
					];
				}
				return $preempt;
			},
			10,
			3
		);

		$result = $this->subscription_controller->create_subscription( true );

		// The loader transient must have been cleaned up.
		$this->assertFalse(
			get_transient( 'rocket_cdn_subscription_creation_in_progress' ),
			'rocket_cdn_subscription_creation_in_progress transient must be deleted on API failure'
		);

		// create_subscription must signal failure.
		$this->assertInstanceOf(
			\WP_Error::class,
			$result,
			'create_subscription() must return WP_Error when the API returns an error'
		);
	}

	// -------------------------------------------------------------------------
	// TC-2.7 – Auto-create free subscription after cancel + delete
	// -------------------------------------------------------------------------

	/**
	 * TC-2.7: After the plugin is updated and the admin page loads, when the
	 * subscription was cancelled and the website was deleted (outside grace
	 * period) and free-tier pages exist, the system auto-creates a free
	 * subscription. Once the task status returns SUCCESS, CDN is enabled and
	 * the free CNAME is applied to the registered pages.
	 */
	public function testShouldAutoCreateFreeSubscriptionAfterCancelAndDelete(): void {
		// --- Populate the free-tier page list (homepage registered before the paid upgrade).
		$query = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );
		$query->add_item(
			[
				'url'           => 'http://example.org/',
				'title'         => 'Home',
				'modified'      => current_time( 'mysql' ),
				'last_accessed' => current_time( 'mysql' ),
			]
		);

		// --- Provide the free-create URL in customer data so CreateAPIClient
		//     resolves a non-empty URL.
		$cdn_token = 'newtoken12345678901234567890123456789';
		$task_id   = 'task_abc_123';

		set_transient(
			'wp_rocket_customer_data',
			(object) [
				'rocketcdn' => (object) [
					'cdn_free_url' => 'https://rocketcdn.me/api/website/create-free/',
				],
			]
		);

		// --- Mock all RocketCDN endpoints.
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) use ( $cdn_token, $task_id ) {
				// subscription/status endpoint – returns 404 (no paid subscription).
				if ( preg_match( '#https://rocketcdn\.me/api/subscription/[^/]+/status#', $url ) ) {
					return [
						'response' => [ 'code' => 404, 'message' => 'Not Found' ],
						'body'     => '',
					];
				}

				// website/search endpoint – returns 404 (website was deleted).
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/search/' ) ) {
					return [
						'response' => [ 'code' => 404, 'message' => 'Not Found' ],
						'body'     => '',
					];
				}

				// Free-subscription creation endpoint.
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/create-free/' ) ) {
					return [
						'response' => [ 'code' => 200, 'message' => 'OK' ],
						'body'     => wp_json_encode(
							[
								'success' => true,
								'data'    => [
									'code'      => 'cdn_task_enqueued',
									'task_id'   => $task_id,
									'cdn_token' => $cdn_token,
								],
							]
						),
					];
				}

				// Task-status endpoint – returns SUCCESS.
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/task/' ) ) {
					return [
						'response' => [ 'code' => 200, 'message' => 'OK' ],
						'body'     => wp_json_encode(
							[
								'success' => true,
								'status'  => 'SUCCESS',
							]
						),
					];
				}

				return $preempt;
			},
			10,
			3
		);

		// --- Trigger subscription creation (skipping the active-subscription guard
		//     since there is none – outside grace period).
		$result = $this->subscription_controller->create_subscription( true );

		$this->assertTrue( $result, 'create_subscription() must succeed' );

		// Token must have been saved.
		$this->assertSame(
			$cdn_token,
			get_option( 'rocketcdn_user_token' ),
			'CDN token must be saved after successful creation'
		);

		// Loader transient must be present (subscription creation is in progress).
		$this->assertNotFalse(
			get_transient( 'rocket_cdn_subscription_creation_in_progress' ),
			'Loader transient must exist while creation task is pending'
		);

		// --- Simulate the Action-Scheduler job: check the task status.
		$this->subscription_controller->check_status( $task_id );

		// After SUCCESS the loader transient must be gone.
		$this->assertFalse(
			get_transient( 'rocket_cdn_subscription_creation_in_progress' ),
			'Loader transient must be removed after task SUCCESS'
		);

		// CDN must now be enabled.
		$settings = get_option( 'wp_rocket_settings', [] );
		$this->assertSame(
			1,
			(int) ( $settings['cdn'] ?? 0 ),
			'CDN must be enabled after successful free subscription creation'
		);
	}

	// -------------------------------------------------------------------------
	// Helpers
	// -------------------------------------------------------------------------

	/**
	 * Registers a pre_http_request filter that maps RocketCDN API endpoints to
	 * the fake responses supplied in $config.
	 */
	private function mock_rocketcdn_api( array $config ): void {
		$subscription_response = $config['subscription_api_response'] ?? null;
		$website_search_response = $config['website_search_response'] ?? null;

		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) use ( $subscription_response, $website_search_response ) {
				// subscription/<domain>/status
				if ( preg_match( '#https://rocketcdn\.me/api/subscription/[^/]+/status#', $url ) ) {
					return $subscription_response ?? $preempt;
				}

				// website/search/?url=...
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/search/' ) ) {
					return $website_search_response ?? $preempt;
				}

				return $preempt;
			},
			10,
			3
		);
	}
}
