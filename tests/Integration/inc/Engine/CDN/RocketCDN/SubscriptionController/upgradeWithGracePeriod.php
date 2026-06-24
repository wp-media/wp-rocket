<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use ReflectionProperty;
use WP_Rocket\Tests\Integration\TestCase;

/**
 *
 * Scenario: A user on an older plugin version (≤ 3.22.0.1) upgrades to a version
 * that includes this check. Their paid subscription was cancelled but the CDN
 * website was not deleted, so it is still in pending_deletion state. The plugin
 * must detect the grace period on first load after the upgrade.
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_UpgradeWithGracePeriod extends TestCase {

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

		$this->assertFalse( $this->subscription_controller->has_active_subscription() );
		$this->assertTrue( $this->subscription_controller->is_in_grace_period() );
		$this->assertFalse( $this->subscription_controller->is_cancelled_outside_grace_period() );
		$this->assertTrue( $this->subscription_controller->is_paid() );

		$this->assertSame( 'rocketcdn_paid', $this->context->get_driver() );

		$html   = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';
		$result = apply_filters( 'rocket_buffer', $html );
		$this->assertStringNotContainsString(
			'.rocketcdn.me',
			$result,
			'CNAME must NOT be applied while CDN is force-paused during grace period'
		);

		$this->assertTrue( $this->render_controller->should_disable_element_for_rocketcdn() );
	}
}
