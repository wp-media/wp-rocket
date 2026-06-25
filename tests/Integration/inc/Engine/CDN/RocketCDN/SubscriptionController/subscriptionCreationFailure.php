<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

/**
 * Scenario: After a cancel + delete (outside grace period), when the plugin
 * attempts to auto-create a free subscription, the creation API returns an
 * error. The loader transient must be cleaned up and the failure must be
 * propagated so no success path runs.
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_SubscriptionCreationFailure extends AbstractSubscriptionControllerTestCase {

	public function set_up() {
		parent::set_up();

		$this->update_rocketcdn_settings(
			[
				'cdn'      => 1,
				'cdn_type' => 'rocketcdn',
			]
		);
		$this->set_rocketcdn_user_token();
	}

	public function testShouldDoAsExpected(): void {
		set_transient(
			'wp_rocket_customer_data',
			(object) [
				'rocketcdn' => (object) [
					'cdn_free_url' => 'https://rocketcdn.me/api/website/create-free/',
				],
			]
		);

		add_filter(
			'pre_http_request',
			function ( $preempt, $args, $url ) {
				// Create endpoint → simulate a server error.
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/create-free/' ) ) {
					return new \WP_Error( 'http_request_failed', 'Internal Server Error' );
				}
				// subscription/status → 404 (no active subscription).
				if ( false !== strpos( $url, 'https://rocketcdn.me/api/subscription/' ) ) {
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

		$result = $this->subscription_controller->create_subscription( true );

		$this->assertFalse( get_transient( 'rocket_cdn_subscription_creation_in_progress' ) );

		$this->assertInstanceOf( \WP_Error::class, $result );
	}
}
