<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use ReflectionProperty;
use WP_Rocket\Tests\Integration\TestCase;

/**
 *
 * Scenario: A paid subscription is cancelled (e.g. via Stripe) but the CDN
 * website is not immediately deleted. The website enters pending_deletion state
 * and a grace period is shown in the UI.
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_CancelPaidAndGracePeriod extends TestCase {

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

	// -------------------------------------------------------------------------
	// Lifecycle
	// -------------------------------------------------------------------------

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
		// Pre-set persistent flag so maybe_pause_cdn_for_inactive_subscription
		// skips the clear_all_cache() side-effect during this test.
		update_option( 'rocket_rocketcdn_forced_pause_state', [ 'persistent' => true ] );

		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				if ( preg_match( '#https://rocketcdn\.me/api/subscription/[^/]+/status#', $url ) ) {
					return [
						'response' => [ 'code' => 404, 'message' => 'Not Found' ],
						'body'     => '',
					];
				}
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/search/' ) ) {
					return [
						'response' => [ 'code' => 200, 'message' => 'OK' ],
						'body'     => json_encode(
							[
								'subscription_status'    => 'cancelled',
								'status'                 => 'pending_deletion',
								'subscription_plan_type' => 'paid',
								'cdn_url'                => self::CDN_URL,
							]
						),
					];
				}
				return $preempt;
			},
			10,
			3
		);

		// Subscription state.
		$this->assertFalse(
			$this->subscription_controller->has_active_subscription(),
			'Subscription must not be active during grace period'
		);
		$this->assertTrue(
			$this->subscription_controller->is_in_grace_period(),
			'Must be in grace period when website is pending deletion'
		);
		$this->assertFalse(
			$this->subscription_controller->is_cancelled_outside_grace_period(),
			'Must NOT be outside grace period while website is still pending deletion'
		);
		$this->assertTrue(
			$this->subscription_controller->is_paid(),
			'Plan type must still be paid during grace period'
		);

		// Context driver resolves to paid (plan_type comes from website/search response).
		$this->assertSame(
			'rocketcdn_paid',
			$this->context->get_driver(),
			'Context driver must be rocketcdn_paid during grace period'
		);

		// CDN is force-paused → no CNAME applied.
		$html   = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';
		$result = apply_filters( 'rocket_buffer', $html );
		$this->assertStringNotContainsString(
			'.rocketcdn.me',
			$result,
			'CNAME must NOT be applied while CDN is force-paused during grace period'
		);

		// Render controller must signal that the RocketCDN element should be disabled.
		$this->assertTrue(
			$this->render_controller->should_disable_element_for_rocketcdn(),
			'RocketCDN element must be disabled while CDN is force-paused'
		);
	}
}
