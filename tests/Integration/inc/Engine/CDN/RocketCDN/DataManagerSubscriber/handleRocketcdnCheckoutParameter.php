<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;
use WP_Rocket\Tests\Integration\DBTrait;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::handle_rocketcdn_checkout_parameter
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_HandleRocketcdnCheckoutParameter extends AdminTestCase {
	use DBTrait;

	/**
	 * Original $_GET superglobal.
	 *
	 * @var array
	 */
	private $original_get;

	/**
	 * DataManagerSubscriber instance.
	 *
	 * @var \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber
	 */
	private $subscriber;

	/**
	 * Installs the RocketCDN page-list table once for the class.
	 *
	 * @return void
	 */
	public static function set_up_before_class() {
		parent::set_up_before_class();
		self::installRocketCDNTable();
	}

	/**
	 * Uninstalls the RocketCDN page-list table after the class.
	 *
	 * @return void
	 */
	public static function tear_down_after_class() {
		self::uninstallRocketCDNTable();
		parent::tear_down_after_class();
	}

	/**
	 * Set up test fixtures.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- Test setup, not processing form data.
		$this->original_get = $_GET;

		// Clean state.
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'wp_rocket_customer_data' );
		delete_transient( 'wpr_user_information_timeout_active' );
		delete_transient( 'wpr_user_information_timeout' );
		self::truncateRocketCDNTable();

		// Get the subscriber from container.
		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'rocketcdn_data_manager_subscriber' );
	}

	/**
	 * Tear down test fixtures.
	 *
	 * @return void
	 */
	public function tear_down() {
		$_GET = $this->original_get;
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'wp_rocket_customer_data' );
		remove_all_filters( 'pre_http_request' );
		self::truncateRocketCDNTable();

		// Reset any cdn_type/cdn a test case may have persisted, so it doesn't bleed
		// into other tests.
		$settings = apply_filters( 'rocket_container', null )->get( 'options_api' )->get( 'settings', [] );
		unset( $settings['cdn_state'], $settings['cdn'], $settings['cdn_type'] );
		apply_filters( 'rocket_container', null )->get( 'options_api' )->set( 'settings', $settings );

		parent::tear_down();
	}

	/**
	 * Test handle_rocketcdn_checkout_parameter method.
	 *
	 * @dataProvider configTestData
	 *
	 * @param array $config   Test configuration.
	 * @param array $expected Expected results.
	 * @return void
	 */
	public function testHandleRocketcdnCheckoutParameter( $config, $expected ) {
		// Set up user with proper role.
		$user_id = self::factory()->user->create( [ 'role' => $config['user_role'] ] );
		wp_set_current_user( $user_id );
		if ( 'administrator' === $config['user_role'] ) {
			$user = wp_get_current_user();
			$user->add_cap( 'rocket_manage_options' );
		}

		// Set up GET parameter.
		if ( $config['parameter_set'] ) {
			$_GET['rocketcdn_checkout'] = 'true';
		} else {
			unset( $_GET['rocketcdn_checkout'] );
		}

		// Set up existing token if needed.
		if ( isset( $config['existing_token'] ) ) {
			update_option( 'rocketcdn_user_token', $config['existing_token'] );
		}

		// Set up user data if provided.
		if ( isset( $config['user_data'] ) ) {
			set_transient( 'wp_rocket_customer_data', (object) $config['user_data'] );
		}

		// Mock API responses if needed.
		if ( isset( $config['api_activation_success'] ) ) {
			$website_id = $config['user_data']['rocketcdn']['rocketcdn_website_id'];

			add_filter(
				'pre_http_request',
				function ( $preempt, $args, $url ) use ( $website_id, $config ) {
					// Mock user data endpoint (called after flush_cache).
					if ( false !== strpos( $url, 'api.wp-rocket.me/stat/1.0/wp-rocket/user.php' ) ) {
						return [
							'response' => [ 'code' => 200 ],
							'body'     => wp_json_encode( $config['user_data'] ),
						];
					}

					// Mock activation endpoint.
					if ( false !== strpos( $url, 'https://rocketcdn.me/api/website/' . $website_id . '/' ) ) {
						if ( $config['api_activation_success'] ) {
							return [
								'response' => [ 'code' => 200 ],
								'body'     => wp_json_encode( [ 'success' => true ] ),
							];
						}
						return [
							'response' => [ 'code' => 500 ],
							'body'     => wp_json_encode( [ 'error' => 'Internal server error' ] ),
						];
					}

					// Mock subscription endpoint.
					if ( false !== strpos( $url, 'https://rocketcdn.me/api/subscription' ) && isset( $config['api_subscription_data'] ) ) {
						$subscription_data                                  = $config['api_subscription_data'];
						$subscription_data['subscription_next_date_update'] = gmdate(
							'Y-m-d H:i:s',
							strtotime( $subscription_data['subscription_next_date_update'] )
						);
						return [
							'response' => [ 'code' => 200 ],
							'body'     => wp_json_encode( $subscription_data ),
						];
					}

					return $preempt;
				},
				10,
				3
			);
		}

		// Set an initial cdn_type before checkout, if configured (Task 8.2 regression).
		// Write straight through options_api (live get_option()/update_option()), not
		// a request-scoped Options_Data snapshot, so it's reliably visible to the
		// subscriber under test regardless of when its own instance was built.
		if ( isset( $config['initial_cdn_type'] ) ) {
			$options_api          = apply_filters( 'rocket_container', null )->get( 'options_api' );
			$settings             = $options_api->get( 'settings', [] );
			$settings['cdn_type'] = $config['initial_cdn_type'];
			$options_api->set( 'settings', $settings );
		}

		// Prefill the page-list table, if configured (page-list preservation regression).
		if ( ! empty( $config['prefill_pages'] ) ) {
			$query = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' );

			for ( $i = 1; $i <= $config['prefill_pages']; $i++ ) {
				$query->add_item(
					[
						'url'           => "http://example.org/page-{$i}",
						'title'         => "Page {$i}",
						'modified'      => current_time( 'mysql' ),
						'last_accessed' => current_time( 'mysql' ),
					]
				);
			}
		}

		$page_count_before = ! empty( $config['prefill_pages'] )
			? apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' )->get_total_count( false )
			: null;

		// Execute the method. Successful/redirecting paths call wp_die() (via
		// WP_ROCKET_IS_TESTING) after persisting their side effects, so catch the
		// exception ourselves instead of expectException() — this lets us keep
		// asserting on the state left behind by the call.
		$exception_thrown = false;

		try {
			$this->subscriber->handle_rocketcdn_checkout_parameter();
		} catch ( \WPDieException $exception ) {
			$exception_thrown = true;
		}

		if ( isset( $expected['expects_redirect'] ) ) {
			$this->assertSame( $expected['expects_redirect'], $exception_thrown );
		}

		// Assert results based on expected outcome.
		if ( isset( $expected['token_stored'] ) && false === $expected['token_stored'] ) {
			$this->assertFalse( get_option( 'rocketcdn_user_token' ) );
		}

		if ( isset( $expected['token_value'] ) ) {
			$this->assertSame( $expected['token_value'], get_option( 'rocketcdn_user_token' ) );
		}

		if ( isset( $expected['cdn_enabled'] ) && $expected['cdn_enabled'] ) {
			$settings = get_option( 'wp_rocket_settings' );
			$this->assertArrayHasKey( 'cdn', $settings );
			$this->assertEquals( 1, $settings['cdn'] );
		}

		if ( isset( $expected['cdn_type'] ) ) {
			$settings = apply_filters( 'rocket_container', null )->get( 'options_api' )->get( 'settings', [] );
			$this->assertArrayHasKey( 'cdn_type', $settings );
			$this->assertSame( $expected['cdn_type'], $settings['cdn_type'] );
		}

		if ( isset( $expected['page_count_unchanged'] ) && $expected['page_count_unchanged'] ) {
			$page_count_after = apply_filters( 'rocket_container', null )->get( 'rocketcdn_query' )->get_total_count( false );
			$this->assertSame( $page_count_before, $page_count_after );
		}
	}
}
