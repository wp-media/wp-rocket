<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use WP_Rocket\Engine\CDN\RocketCDN\SubscriptionController;
use WP_Rocket\Engine\CDN\Context;

/**
 * Scenario: A paid RocketCDN subscription is cancelled and the website is
 * deleted from the CDN app (outside the grace period).
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_CancelPaidAndDelete extends AbstractSubscriptionControllerTestCase {

	/**
	 * @var Context
	 */
	private $context;

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

		$this->context = $this->getRocketContainer()->get( 'cdn_context' );

		self::truncateRocketCDNTable();

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
		self::truncateRocketCDNTable();

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		// Populate free-tier pages that existed before the paid upgrade.
		if ( ! empty( $config['free_pages'] ) ) {
			$query = $this->getRocketContainer()->get( 'rocketcdn_query' );
			foreach ( $config['free_pages'] as $page ) {
				$query->add_item(
					[
						'url'           => $page['url'],
						'title'         => $page['title'],
						'modified'      => current_time( 'mysql' ),
						'last_accessed' => current_time( 'mysql' ),
					]
				);
			}
		}

		// Both endpoints return 404: subscription cancelled and website deleted.
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
		$this->assertFalse( $this->subscription_controller->is_paid() );
		$this->assertSame( 'rocketcdn',  $this->context->get_driver() );

		// No active subscription. FrontendSubscriber returns empty CNAMEs no rewriting.
		$result = apply_filters( 'rocket_buffer', $config['html'] );
		$this->assertStringNotContainsString( '.rocketcdn.me',  $result );

		// Pages added before the paid upgrade must still be in the free-tier DB.
		if ( array_key_exists( 'free_pages_count_in_db', $expected ) ) {
			$query = $this->getRocketContainer()->get( 'rocketcdn_query' );
			$this->assertCount( $expected['free_pages_count_in_db'],  $query->query( [] ) );
		}
	}
}
