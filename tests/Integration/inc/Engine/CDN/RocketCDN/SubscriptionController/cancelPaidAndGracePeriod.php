<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * Scenario: A paid subscription is cancelled (e.g. via Stripe) but the CDN
 * website is not immediately deleted. The website enters pending_deletion state
 * and a grace period is shown in the UI.
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_CancelPaidAndGracePeriod extends AbstractSubscriptionControllerTestCase {

	public const CDN_URL = 'https://abcd1234.delivery.rocketcdn.me';

	private $context;

	private $render_controller;

	public function set_up() {
		parent::set_up();

		$container = $this->getRocketContainer();

		$this->context           = $container->get( 'cdn_context' );
		$this->render_controller = $container->get( 'cdn_render_controller' );

		$this->update_rocketcdn_settings(
			[
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			]
		);
		$this->set_rocketcdn_user_token();

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'rewrite', 2 );
	}

	public function tear_down() {
		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
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
						'response' => [
							'code'    => 404,
							'message' => 'Not Found',
						],
						'body'     => '',
					];
				}
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/search/' ) ) {
					return [
						'response' => [
							'code'    => 200,
							'message' => 'OK',
						],
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
		$this->assertFalse( $this->subscription_controller->has_active_subscription() );
		$this->assertTrue( $this->subscription_controller->is_in_grace_period() );
		$this->assertFalse( $this->subscription_controller->is_cancelled_outside_grace_period() );
		$this->assertTrue( $this->subscription_controller->is_paid() );

		// Context driver resolves to paid (plan_type comes from website/search response).
		$this->assertSame( 'rocketcdn_paid',  $this->context->get_driver() );

		// CDN is force-paused → no CNAME applied.
		$html   = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';
		$result = apply_filters( 'rocket_buffer', $html );
		$this->assertStringNotContainsString( '.rocketcdn.me', $result );

		// Render controller must signal that the RocketCDN element should be disabled.
		$this->assertTrue( $this->render_controller->should_disable_element_for_rocketcdn() );
	}
}
