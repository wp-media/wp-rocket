<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * Scenario: After updating from 3.21.3, the paid subscription was already
 * cancelled and the website deleted before the update. CDN was enabled and the
 * old RocketCDN CNAME is still stored as a BYOCDN CNAME. The plugin must
 * preserve this "broken/orphan" state: BYOCDN is shown as active and the CNAME
 * is still applied (no active CDN backend behind it).
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_Update321CDNEnabled extends AbstractSubscriptionControllerTestCase {

	public const CDN_URL = 'https://abcd1234.delivery.rocketcdn.me';

	private $context;

	public function set_up() {
		parent::set_up();

		$this->context = $this->getRocketContainer()->get( 'cdn_context' );

		// Simulate 3.21.3 saved state: CDN on, BYOCDN type, old RocketCDN CNAME preserved.
		$this->update_rocketcdn_settings(
			[
				'cdn'        => 1,
				'cdn_type'   => 'byocdn',
				'cdn_cnames' => [ self::CDN_URL ],
			]
		);

		// Override via pre-filters to bypass any Options_Data singleton caching.
		$this->set_cdn_option_overrides( 1, 'byocdn', [ self::CDN_URL ] );

		$this->unregisterAllCallbacksExcept( 'rocket_buffer', 'rewrite', 2 );
	}

	public function tear_down() {
		$this->clear_cdn_option_overrides();

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	public function testShouldDoAsExpected(): void {
		// Both endpoints return 404: no active subscription and website was deleted.
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

		// Context short-circuits to BYOCDN because cdn_type != rocketcdn.
		$this->assertSame( 'byocdn', $this->context->get_driver() );

		// Prefilled CNAME is preserved and still applied to the frontend.
		$html   = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';
		$result = apply_filters( 'rocket_buffer', $html );
		$this->assertStringContainsString( '.rocketcdn.me', $result );
	}
}
