<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 *
 * Scenario: After updating from 3.21.3, the subscription was cancelled from
 * the account and the website was deleted. CDN was left disabled (cdn=0) and
 * no custom CNAME was ever configured. The simplest possible post-update state:
 * CDN is off and nothing to rewrite with, so no CNAME is applied.
 *
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_Update321CdnDisabledCnameEmpty extends AbstractSubscriptionControllerTestCase {

	private $context;

	public function set_up() {
		parent::set_up();

		$this->context = $this->getRocketContainer()->get( 'cdn_context' );

		// Simulate 3.21.3 saved state: CDN off, RocketCDN type, no CNAME.
		$this->update_rocketcdn_settings(
			[
				'cdn'        => 0,
				'cdn_type'   => 'rocketcdn',
				'cdn_cnames' => [],
			]
		);

		// Override via pre-filters to bypass any Options_Data singleton caching.
		add_filter( 'pre_get_rocket_option_cdn', fn() => 0, 5 );
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

	/**
	 * TC-5.4: The simplest cancelled-outside-grace-period state — CDN disabled
	 * and no CNAME configured. The rewrite check gates out immediately at
	 * is_cdn_enabled()=false and no CNAME is applied.
	 */
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

		// CDN is disabled — no CNAME can be applied regardless.
		$html   = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';
		$result = apply_filters( 'rocket_buffer', $html );
		$this->assertStringNotContainsString( '.rocketcdn.me',  $result );
	}
}
