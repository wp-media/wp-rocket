<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Rocket\Tests\Integration\AdminTestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::maybe_retry_activation
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_MaybeRetryActivation extends AdminTestCase {

	/**
	 * Original user ID.
	 *
	 * @var int
	 */
	private $original_user_id;

	/**
	 * DataManagerSubscriber instance.
	 *
	 * @var \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber
	 */
	private $subscriber;

	/**
	 * Track API call count for mocking.
	 *
	 * @var int
	 */
	private $subscription_api_call_count = 0;

	/**
	 * Flag to indicate if activation was called.
	 *
	 * @var bool
	 */
	private $activation_api_called = false;

	public function set_up() {
		parent::set_up();

		$this->original_user_id = get_current_user_id();

		// Clean state.
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'rocketcdn_status' );
		delete_transient( 'wp_rocket_customer_data' );
		$this->reset_wp_rocket_settings();

		// Reset counters.
		$this->subscription_api_call_count = 0;
		$this->activation_api_called       = false;

		// Get the subscriber from container.
		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'rocketcdn_data_manager_subscriber' );
	}

	public function tear_down() {
		wp_set_current_user( $this->original_user_id );
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'rocketcdn_status' );
		delete_transient( 'wp_rocket_customer_data' );
		remove_all_filters( 'pre_http_request' );
		$this->reset_wp_rocket_settings();

		parent::tear_down();
	}

	/**
	 * Reset wp_rocket_settings to clean state.
	 */
	private function reset_wp_rocket_settings() {
		$settings = get_option( 'wp_rocket_settings', [] );
		if ( ! empty( $settings ) ) {
			unset( $settings['cdn'], $settings['cdn_cnames'], $settings['cdn_zone'] );
			update_option( 'wp_rocket_settings', $settings );
		}
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldHandleRetryActivation( $config, $expected ) {
		// Set up user.
		if ( isset( $config['user_role'] ) ) {
			$user_id = $this->factory->user->create( [ 'role' => $config['user_role'] ] );
			wp_set_current_user( $user_id );
			if ( 'administrator' === $config['user_role'] ) {
				$user = wp_get_current_user();
				$user->add_cap( 'rocket_manage_options' );
			}
		}

		// Set up token.
		if ( isset( $config['token'] ) ) {
			update_option( 'rocketcdn_user_token', $config['token'] );
		}

		// Mock API responses.
		if ( isset( $config['subscription_data'] ) || isset( $config['activation_success'] ) || isset( $config['user_data'] ) ) {
			$test = $this;
			add_filter(
				'pre_http_request',
				function ( $preempt, $args, $url ) use ( $config, $test ) {
					// Mock user endpoint (WP Rocket user.php).
					if ( false !== strpos( $url, 'https://api.wp-rocket.me/stat/1.0/wp-rocket/user.php' ) ) {
						if ( isset( $config['user_data'] ) ) {
							return [
								'response' => [ 'code' => 200 ],
								'body'     => wp_json_encode( $config['user_data'] ),
							];
						}

						// Return empty response if no user_data is configured.
						return [
							'response' => [ 'code' => 404 ],
							'body'     => '',
						];
					}

					// Mock subscription endpoint (subscription/domain/status/).
					if ( false !== strpos( $url, 'https://rocketcdn.me/api/subscription/example.org/status' ) ) {
						$test->subscription_api_call_count++;

						// After activation was called, return the post-activation data.
						if (
							$test->activation_api_called
							&& isset( $config['subscription_data_after_activation'] )
						) {
							$subscription_data = $config['subscription_data_after_activation'];
						} else {
							$subscription_data = $config['subscription_data'];
						}

						if ( isset( $subscription_data['subscription_next_date_update'] ) ) {
							$subscription_data['subscription_next_date_update'] = gmdate(
								'Y-m-d H:i:s',
								strtotime( $subscription_data['subscription_next_date_update'] )
							);
						}

						return [
							'response' => [ 'code' => 200 ],
							'body'     => wp_json_encode( $subscription_data ),
						];
					}

					// Mock activation endpoint (website/<id>/).
					if ( preg_match( '/https:\/\/rocketcdn\.me\/api\/website\/\d+\/$/', $url ) ) {
						$test->activation_api_called = true;

						if ( isset( $config['activation_success'] ) && $config['activation_success'] ) {
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

					return $preempt;
				},
				10,
				3
			);
		}

		// Execute the method.
		$this->subscriber->maybe_retry_activation();

		// Assert CDN state.
		$settings = get_option( 'wp_rocket_settings', [] );

		if ( isset( $expected['cdn_enabled'] ) && $expected['cdn_enabled'] ) {
			$this->assertArrayHasKey( 'cdn', $settings );
			$this->assertEquals( 1, $settings['cdn'] );
			$this->assertArrayHasKey( 'cdn_cnames', $settings );
			$this->assertContains( $expected['cdn_url'], $settings['cdn_cnames'] );
		} else {
			// CDN should NOT be enabled.
			$cdn_enabled = isset( $settings['cdn'] ) && 1 === (int) $settings['cdn'];
			$this->assertFalse( $cdn_enabled, 'CDN should not be enabled in this scenario' );
		}

		// Assert token was saved when it came from user endpoint and activation succeeded.
		if ( isset( $config['user_data'] ) && isset( $expected['cdn_enabled'] ) && $expected['cdn_enabled'] ) {
			$saved_token = get_option( 'rocketcdn_user_token' );
			$this->assertNotEmpty( $saved_token, 'Token should be saved after successful activation' );
			$this->assertSame( $config['user_data']->rocketcdn->cdn_token, $saved_token );
		}
	}

	private function mock_subscription() {

	}
}
