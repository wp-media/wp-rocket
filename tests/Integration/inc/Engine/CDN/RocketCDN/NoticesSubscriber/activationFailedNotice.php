<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WP_Rocket\Engine\License\API\UserClient;
use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;
use WPMedia\PHPUnit\Integration\HttpRequestTrait;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::activation_failed_notice
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_ActivationFailedNotice extends TestCase {
	use HttpRequestTrait;

	/**
	 * Exact URL of the RocketCDN website-search endpoint for the default test home host.
	 */
	const SEARCH_ENDPOINT = 'https://rocketcdn.me/api/website/search/?url=http://example.org';

	/**
	 * Original user ID.
	 *
	 * @var int
	 */
	private $original_user_id;

	private $test_config;

	/**
	 * NoticesSubscriber instance.
	 *
	 * @var \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber
	 */
	private $subscriber;

	public function set_up() {
		parent::set_up();

		$this->setup_http();

		// Don't trigger modules that depend on the current_screen hook.
		$this->unregisterAllCallbacks( 'current_screen' );

		// Ensure admin notices file is loaded (contains rocket_notice_html function).
		if ( ! function_exists( 'rocket_notice_html' ) ) {
			require_once WP_ROCKET_ADMIN_UI_PATH . 'notices.php';
		}

		$this->original_user_id = get_current_user_id();
		set_current_screen( 'settings_page_wprocket' );

		// Get the subscriber from container.
		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'rocketcdn_notices_subscriber' );

		add_filter( 'pre_transient_rocketcdn_status', [ $this, 'mock_rocketcdn_status_transient' ] );
	}

	public function tear_down() {
		wp_set_current_user( $this->original_user_id );
		delete_option( 'rocketcdn_user_token' );
		delete_transient( 'rocketcdn_status' );
		delete_transient( 'wp_rocket_customer_data' );
		remove_filter( 'pre_transient_rocketcdn_status', [ $this, 'mock_rocketcdn_status_transient' ] );

		$this->restoreWpHook( 'current_screen' );

		$this->tear_down_http();

		parent::tear_down();
	}

	/**
	 * Get the captured notice output by calling method directly.
	 *
	 * @return string
	 */
	private function get_actual_notice() {
		ob_start();
		$this->subscriber->activation_failed_notice();
		return ob_get_clean();
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDisplayOrBailBasedOnConfig( $config, $expected ) {
		$this->test_config = $config;

		// Set up user.
		if ( isset( $config['user_role'] ) ) {
			$user_id = $this->factory->user->create( [ 'role' => $config['user_role'] ] );
			wp_set_current_user( $user_id );
			if ( 'administrator' === $config['user_role'] ) {
				$user = wp_get_current_user();
				$user->add_cap( 'rocket_manage_options' );
			}
		}

		// Set up token if specified in config.
		if ( isset( $config['token'] ) ) {
			update_option( 'rocketcdn_user_token', $config['token'] );
		}

		// Set up screen.
		if ( isset( $config['current_screen'] ) ) {
			set_current_screen( $config['current_screen'] );
		}

		// Set up white label.
		$this->white_label = $config['white_label'] ?? false;

		// Set up user data for express checkout URL.
		if ( isset( $config['user_data'] ) ) {
			// Convert nested arrays to objects to match actual API response structure.
			$user_data = json_decode( wp_json_encode( $config['user_data'] ) );
			set_transient( 'wp_rocket_customer_data', $user_data, MINUTE_IN_SECONDS );
		}

		// Register URL fixtures for the paths the notice's checks may reach. The
		// `rocketcdn_status` transient short-circuit above already answers most
		// subscription-status reads, so these rarely fire in practice; they exist so a
		// regression that removes that short-circuit fails loudly instead of hitting the
		// network.
		if ( isset( $config['subscription_data'] ) ) {
			$this->config['http'][ self::SEARCH_ENDPOINT ] = [
				'response' => [ 'code' => 200 ],
				'body'     => wp_json_encode( $config['subscription_data'] ),
			];
		}

		if ( isset( $config['user_data'] ) ) {
			$this->config['http'][ UserClient::USER_ENDPOINT ] = [
				'response' => [ 'code' => 200 ],
				'body'     => wp_json_encode( $config['user_data'] ),
			];
		}

		$actual = $this->get_actual_notice();

		if ( isset( $expected['should_display'] ) && $expected['should_display'] ) {
			$this->assertStringContainsString( 'rocketcdn_activation_failed_notice', $actual );
			$this->assertStringContainsString( 'RocketCDN activation incomplete', $actual );
			$this->assertStringContainsString( 'Complete activation', $actual );

			if ( isset( $expected['express_checkout_url_contains'] ) ) {
				$this->assertStringContainsString( $expected['express_checkout_url_contains'], $actual );
			}
		} else {
			$this->assertEmpty( $actual );
		}
	}

	public function mock_rocketcdn_status_transient( $pre ) {
		if ( empty( $this->test_config['subscription_data'] ) ){
			return $pre;
		}
		return $this->test_config['subscription_data'];
	}
}
