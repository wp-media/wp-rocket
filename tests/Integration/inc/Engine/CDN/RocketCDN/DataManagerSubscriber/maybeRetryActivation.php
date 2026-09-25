<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\DataManagerSubscriber;

use WP_Error;
use WP_Rocket\Engine\CDN\RocketCDN\APIClient;
use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\AdminTestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\DataManagerSubscriber::maybe_retry_activation
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_MaybeRetryActivation extends AdminTestCase {
	use HttpRequestTrait;

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
	 * Whether an outbound HTTP request was attempted, set by count_any_request().
	 *
	 * @var bool
	 */
	private $api_request_made = false;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		// Don't trigger modules that depend on the current_screen hook.
		$this->unregisterAllCallbacks( 'current_screen' );

		$this->original_user_id = get_current_user_id();

		// Clean state.
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'rocketcdn_status' );
		delete_transient( 'wp_rocket_customer_data' );
		$this->reset_wp_rocket_settings();

		// This method only runs on the WP Rocket settings page; scenarios that need a
		// different screen (e.g. the screen-guard test) override this explicitly.
		set_current_screen( 'settings_page_wprocket' );

		// Get the subscriber from container.
		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'rocketcdn_data_manager_subscriber' );
	}

	public function tear_down() {
		wp_set_current_user( $this->original_user_id );
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'rocketcdn_status' );
		delete_transient( 'wp_rocket_customer_data' );
		$this->reset_wp_rocket_settings();

		$this->restoreWpHook( 'current_screen' );

		$this->tear_down_http();

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

		// Register the user-data fixture unconditionally: it's the only one every data set
		// may reach (the fallback lookup when no token is saved locally).
		$this->config['http'][ UserClient::USER_ENDPOINT ] = isset( $config['user_data'] )
			? [
				'response' => [ 'code' => 200 ],
				'body'     => wp_json_encode( $config['user_data'] ),
			]
			: [
				'response' => [ 'code' => 404 ],
				'body'     => '',
			];

		if ( isset( $config['subscription_data'] ) ) {
			$build_subscription_response = static function ( array $subscription_data ) {
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
			};

			$subscription_url = sprintf( '%1$ssubscription/%2$s/status', APIClient::ROCKETCDN_API, 'example.org' );

			// A successful retry calls get_subscription_data() twice: once before the retry,
			// once after. The trait's list-response form answers each call in turn.
			$this->config['http'][ $subscription_url ] = isset( $config['subscription_data_after_activation'] )
				? [
					$build_subscription_response( $config['subscription_data'] ),
					$build_subscription_response( $config['subscription_data_after_activation'] ),
				]
				: $build_subscription_response( $config['subscription_data'] );
		}

		if ( isset( $config['activation_success'], $config['subscription_data']['website_id'] ) ) {
			$this->config['http'][ APIClient::ROCKETCDN_API . 'website/' . $config['subscription_data']['website_id'] . '/' ] = $config['activation_success']
				? [
					'response' => [ 'code' => 200 ],
					'body'     => wp_json_encode( [ 'success' => true ] ),
				]
				: [
					'response' => [ 'code' => 500 ],
					'body'     => wp_json_encode( [ 'error' => 'Internal server error' ] ),
				];
		}

		// Execute the method.
		$this->subscriber->maybe_retry_activation();

		// Assert CDN state.
		$settings = get_option( 'wp_rocket_settings', [] );

		if ( isset( $expected['cdn_enabled'] ) && $expected['cdn_enabled'] ) {
			$this->assertArrayHasKey( 'cdn', $settings );
			$this->assertEquals( 1, $settings['cdn'] );
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

	/**
	 * The method must bail before touching the API or CDN state when the current
	 * screen isn't the WP Rocket settings page, even with an otherwise-valid,
	 * would-succeed configuration.
	 */
	public function testShouldBailWhenNotOnRocketSettingsPage() {
		$user_id = $this->factory->user->create( [ 'role' => 'administrator' ] );
		$user    = wp_set_current_user( $user_id );
		$user->add_cap( 'rocket_manage_options' );

		update_option( 'rocketcdn_user_token', '1234567890123456789012345678901234567890' );

		set_current_screen( 'edit.php' );

		// No fixture entry: an empty http config already fails the test on any request.
		// The inspector also gives an assertion with a specific failure message.
		add_filter( 'pre_http_request', [ $this, 'count_any_request' ], 9, 3 );

		$this->subscriber->maybe_retry_activation();

		remove_filter( 'pre_http_request', [ $this, 'count_any_request' ], 9 );

		$this->assertFalse( $this->api_request_made, 'No RocketCDN API request should be made when not on the WP Rocket settings page.' );

		$settings    = get_option( 'wp_rocket_settings', [] );
		$cdn_enabled = isset( $settings['cdn'] ) && 1 === (int) $settings['cdn'];
		$this->assertFalse( $cdn_enabled, 'CDN should not be enabled when the guard bails on a non-WP Rocket screen' );
	}

	/**
	 * Records that an outbound HTTP request was attempted, without altering the response.
	 * Registered at priority 9, ahead of the trait's own mock at priority 10.
	 *
	 * @param false|array|WP_Error $response Preemptive response as received.
	 * @param array                $args     Unused request arguments.
	 * @param string               $url      Unused requested URL.
	 *
	 * @return false|array|WP_Error
	 */
	public function count_any_request( $response, $args, $url ) {
		$this->api_request_made = true;

		return $response;
	}
}
