<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * Scenario: The user re-purchases a paid CDN subscription after having previously
 * been refunded or cancelled. The subscription is now active and running.
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_RepurchasedPaidActive extends AbstractSubscriptionControllerTestCase {

	public const CDN_URL = 'https://abcd1234.delivery.rocketcdn.me';

	private $context;

	public function set_up() {
		parent::set_up();

		$container = $this->getRocketContainer();

		$this->context = $container->get( 'cdn_context' );

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
		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				if ( preg_match( '#https://rocketcdn\.me/api/subscription/[^/]+/status#', $url ) ) {
					return [
						'response' => [
							'code'    => 200,
							'message' => 'OK',
						],
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
	}
}
