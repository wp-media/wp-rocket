<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::handle_rocketcdn_checkout_parameter
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_HandleRocketcdnCheckoutParameter extends AdminTestCase {

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
						$subscription_data = $config['api_subscription_data'];
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

		// The method always ends in a redirect (wp_die() in test env), so catch it locally
		// instead of using expectException(): that would unwind the stack past every
		// assertion below before it runs, silently making them all dead code.
		$redirected = false;

		try {
			$this->subscriber->handle_rocketcdn_checkout_parameter();
		} catch ( \WPDieException $e ) {
			$redirected = true;
		}

		if ( isset( $expected['expects_redirect'] ) && $expected['expects_redirect'] ) {
			$this->assertTrue( $redirected, 'Expected handle_rocketcdn_checkout_parameter() to redirect.' );
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
	}
}
