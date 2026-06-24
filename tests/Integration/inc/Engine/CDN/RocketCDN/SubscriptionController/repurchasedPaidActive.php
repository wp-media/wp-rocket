<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use ReflectionProperty;
use WP_Rocket\Tests\Integration\TestCase;

/**
 *
 * Scenario: The user re-purchases a paid CDN subscription after having previously
 * been refunded or cancelled. The subscription is now active and running.
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_RepurchasedPaidActive extends TestCase {

	protected static $use_settings_trait = true;

	protected static $transients = [
		'rocketcdn_status'          => null,
		'rocket_cdn_website_search' => null,
	];

	public const TOKEN   = '1234567890123456789012345678901234567890';
	public const CDN_URL = 'https://abcd1234.delivery.rocketcdn.me';

	private $subscription_controller;

	private $context;

	private $render_controller;

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

		// Reset the FrontendSubscriber's per-request memoized CDN URL.
		$frontend = $container->get( 'rocketcdn_frontend_subscriber' );
		$prop     = new ReflectionProperty( $frontend, 'rocketcdn_url' );
		$prop->setValue( $frontend, null );

		$settings             = get_option( 'wp_rocket_settings', [] );
		$settings['cdn']      = 1;
		$settings['cdn_type'] = 'rocketcdn';
		update_option( 'wp_rocket_settings', $settings );

		update_option( 'rocketcdn_user_token', self::TOKEN );

		set_current_screen( 'settings_page_wprocket' );
		add_filter( 'home_url', [ $this, 'home_url_cb' ] );

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'rewrite', 2 );
	}

	public function tear_down() {
		remove_all_filters( 'pre_http_request' );
		remove_filter( 'home_url', [ $this, 'home_url_cb' ] );

		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_website_search' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );

		set_current_screen( 'front' );

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	public function home_url_cb(): string {
		return 'http://example.org';
	}


	public function testShouldDoAsExpected(): void {
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				if ( preg_match( '#https://rocketcdn\.me/api/subscription/[^/]+/status#', $url ) ) {
					return [
						'response' => [ 'code' => 200, 'message' => 'OK' ],
						'body'     => json_encode(
							[
								'success'           => true,
								'website_id'        => 12345,
								'website_activated' => true,
								'cdn_url'           => self::CDN_URL,
								'status'            => 'running',
								'next_date_update'  => '2026-12-01 00:00:00',
								'website_attached'  => true,
								'plan_type'         => 'paid',
								'plan_page_limit'   => null,
								'subscription_id'   => 67890,
							]
						),
					];
				}
				return $preempt;
			},
			10,
			3
		);

		$this->assertTrue( $this->subscription_controller->has_active_subscription() );
		$this->assertFalse( $this->subscription_controller->is_in_grace_period() );
		$this->assertFalse( $this->subscription_controller->is_cancelled_outside_grace_period() );
		$this->assertTrue( $this->subscription_controller->is_paid() );

		$this->assertSame( 'rocketcdn_paid', $this->context->get_driver() );

		/*$html   = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';
		$result = apply_filters( 'rocket_buffer', $html );

		$this->assertStringContainsString( '.rocketcdn.me', $result );*/

		//$this->assertFalse( $this->render_controller->should_disable_element_for_rocketcdn() );
	}
}
