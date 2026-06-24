<?php
declare( strict_types=1 );

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\SubscriptionController;

use ReflectionProperty;
use WP_Rocket\Tests\Integration\TestCase;

/**
 *
 * Scenario: The user requests a refund; the subscription is cancelled and the
 * website is deleted from the CDN (outside the grace period).
 *
 *
 * @group RocketCDN
 * @group CDN
 * @group AdminOnly
 */
class Test_RefundAndDeleteWebsite extends TestCase {

	protected static $use_settings_trait = true;

	protected static $transients = [
		'rocketcdn_status'          => null,
		'rocket_cdn_website_search' => null,
	];

	public const TOKEN = '1234567890123456789012345678901234567890';

	private $subscription_controller;

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

		$container = apply_filters( 'rocket_container', null );

		$this->subscription_controller = $container->get( 'rocketcdn_subscription_controller' );
		$this->context                 = $container->get( 'cdn_context' );

		delete_transient( 'rocketcdn_status' );
		delete_transient( 'rocket_cdn_website_search' );
		delete_transient( 'rocket_cdn_subscription_creation_in_progress' );
		delete_transient( 'rocket_cdn_create_request' );
		delete_transient( 'rocket_cdn_check_status_request' );
		delete_option( 'rocketcdn_user_token' );
		delete_option( 'rocket_rocketcdn_forced_pause_state' );
		delete_transient( 'wp_rocket_customer_data' );

		// Reset the FrontendSubscriber's per-request memoized CDN URL.
		$frontend = $container->get( 'rocketcdn_frontend_subscriber' );
		$prop     = new ReflectionProperty( $frontend, 'rocketcdn_url' );
		$prop->setValue( $frontend, null );

		self::truncateRocketCDNTable();

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

		self::truncateRocketCDNTable();
		set_current_screen( 'front' );

		$this->restoreWpHook( 'rocket_buffer' );

		parent::tear_down();
	}

	public function home_url_cb(): string {
		return 'http://example.org';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoAsExpected( array $config, array $expected ): void {
		if ( ! empty( $config['free_pages'] ) ) {
			$query = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );
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
						'response' => [ 'code' => 404, 'message' => 'Not Found' ],
						'body'     => '',
					];
				}
				return $preempt;
			},
			10,
			3
		);

		$this->assertFalse(
			$this->subscription_controller->has_active_subscription(),
			'Subscription must not be active after refund + delete'
		);
		$this->assertFalse(
			$this->subscription_controller->is_in_grace_period(),
			'Must not be in grace period when website is deleted'
		);
		$this->assertTrue(
			$this->subscription_controller->is_cancelled_outside_grace_period(),
			'Must be outside grace period after refund + website deletion'
		);
		$this->assertFalse(
			$this->subscription_controller->is_paid(),
			'Plan type must not be paid after refund + delete'
		);

		$this->assertSame(
			'rocketcdn',
			$this->context->get_driver(),
			'Context driver must be base rocketcdn (free UI) after refund + delete'
		);

		$html   = '<html><body><img src="http://example.org/wp-content/uploads/test.jpg"></body></html>';
		$result = apply_filters( 'rocket_buffer', $html );
		$this->assertStringNotContainsString(
			'.rocketcdn.me',
			$result,
			'CNAME must NOT be applied when there is no active subscription'
		);

		// Pages added before the paid subscription must still be in the free-tier DB.
		if ( array_key_exists( 'free_pages_count_in_db', $expected ) ) {
			$query = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );
			$this->assertCount(
				$expected['free_pages_count_in_db'],
				$query->query( [] ),
				'Pre-paid free pages must be preserved in the DB after refund + delete'
			);
		}
	}
}
