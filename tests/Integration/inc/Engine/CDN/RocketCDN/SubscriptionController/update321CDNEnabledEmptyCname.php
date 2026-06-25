<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * TC-5.3 — Update from 3.21.3: CDN enabled · RocketCDN type · CNAME empty.
 *
 * Scenario: After updating from 3.21.3, the subscription was cancelled from
 * the account and the website was deleted. CDN was left enabled (cdn=1) and
 * cdn_type='rocketcdn', but no custom CNAME was ever configured. Since there
 * is no active subscription and FrontendSubscriber's RocketCDN path returns []
 * when has_active_subscription() is false, no CNAME is applied to the frontend.
 *
 * API state:
 *   subscription/status → 404
 *   website/search      → 404
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_Update321CdnEnabledCnameEmpty extends AbstractSubscriptionControllerTestCase {

	private $context;

	public function set_up() {
		parent::set_up();

		$this->context = $this->getRocketContainer()->get( 'cdn_context' );

		// Simulate 3.21.3 saved state: CDN on, RocketCDN type, no custom CNAME.
		$this->update_rocketcdn_settings(
			[
				'cdn'        => 1,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [],
			]
		);

		// Override via pre-filters to bypass any Options_Data singleton caching.
		add_filter( 'pre_get_rocket_option_cdn', fn() => 1, 5 );
		add_filter( 'pre_get_rocket_option_cdn_type', fn() => 'rocketcdn', 5 );
		add_filter( 'pre_get_rocket_option_cdn_cnames', fn() => [], 5 );

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'rewrite', 2 );
	}

	public function tear_down() {
		remove_all_filters( 'pre_get_rocket_option_cdn' );
		remove_all_filters( 'pre_get_rocket_option_cdn_type' );
		remove_all_filters( 'pre_get_rocket_option_cdn_cnames' );

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	public function testShouldDoAsExpected(): void {
		// Both endpoints return 404: no active subscription, website was deleted.
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

		// Subscription state.
		$this->assertFalse( $this->subscription_controller->has_active_subscription() );
		$this->assertFalse( $this->subscription_controller->is_in_grace_period() );

		$this->assertTrue( $this->subscription_controller->is_cancelled_outside_grace_period() );

		// Context returns the base 'rocketcdn' state.
		$this->assertSame( 'rocketcdn',  $this->context->get_driver() );

		// CDN is enabled but no subscription and no CNAME — nothing to inject.
		$html   = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';
		$result = apply_filters( 'rocket_buffer', $html );
		$this->assertStringNotContainsString( '.rocketcdn.me',  $result );
	}
}
