<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * Scenario
 *   After updating from 3.21.3, the subscription was cancelled from
 *   the account and the website was deleted. CDN was left disabled (cdn=0) with
 *   cdn_type still set to 'rocketcdn'. The stored CNAME is ignored while
 *   cdn_type='rocketcdn' and there is no active subscription; no CNAME is
 *   applied to the frontend.
 *
 *   The user manually switches to BYOCDN in the admin (cdn=1,
 *   cdn_type='byocdn'). The prefilled CNAME is now passed through unchanged by
 *   FrontendSubscriber and IS applied to the frontend.
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_Update321CdnDisabled extends AbstractSubscriptionControllerTestCase {

	public const CDN_URL = 'https://abcd1234.delivery.rocketcdn.me';

	private $context;

	public function set_up() {
		parent::set_up();

		$this->context = $this->getRocketContainer()->get( 'cdn_context' );

		// Simulate 3.21.3 saved state: CDN off, cdn_type still rocketcdn, CNAME stored.
		$this->update_rocketcdn_settings(
			[
				'cdn'        => 0,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [ self::CDN_URL ],
			]
		);

		// Phase 1 option overrides (CDN disabled, RocketCDN type).
		$this->set_cdn_option_overrides( 0, 'rocketcdn', [ self::CDN_URL ] );

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'rewrite', 2 );
	}

	public function tear_down() {
		$this->clear_cdn_option_overrides();

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	public function testShouldDoAsExpected(): void {
		$html = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';

		// Both endpoints return 404 throughout both phases.
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
							'code'    => 404,
							'message' => 'Not Found',
						],
						'body'     => '',
					];
				}
				return $preempt;
			},
			10,
			3
		);

		$this->assertFalse( $this->subscription_controller->has_active_subscription() );
		$this->assertFalse( $this->subscription_controller->is_in_grace_period() );
		$this->assertTrue( $this->subscription_controller->is_cancelled_outside_grace_period() );

		// Context returns the base 'rocketcdn' state (cancelled, no subscription).
		$this->assertSame( 'rocketcdn',  $this->context->get_driver() );

		// CDN is disabled — no CNAME must be applied.
		$result_phase1 = apply_filters( 'rocket_buffer', $html );
		$this->assertStringNotContainsString( '.rocketcdn.me',  $result_phase1 );

		$this->clear_cdn_option_overrides();
		$this->set_cdn_option_overrides( 1, 'byocdn', [ self::CDN_URL ] );

		// Reset FrontendSubscriber CDN URL.
		$this->reset_frontend_subscriber_memo( $this->getRocketContainer() );

		// Context now short-circuits to BYOCDN because cdn_type != rocketcdn.
		$this->assertSame( 'byocdn', $this->context->get_driver() );

		// Prefilled CNAME must now be applied to the frontend.
		$result_phase2 = apply_filters( 'rocket_buffer', $html );
		$this->assertStringContainsString( '.rocketcdn.me', $result_phase2 );
	}
}
