<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::display_pro_detection_failure_notice
 *
 * @group AdminOnly
 * @group RocketCDN
 */
class Test_DisplayProDetectionFailureNotice extends TestCase {

	/**
	 * Original user ID.
	 *
	 * @var int
	 */
	private $original_user_id;

	/**
	 * NoticesSubscriber instance.
	 *
	 * @var \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber
	 */
	private $subscriber;

	/**
	 * Sets up the test fixture.
	 *
	 * @return void
	 */
	public function set_up() {
		parent::set_up();

		// Ensure admin notices file is loaded (contains rocket_notice_html function).
		if ( ! function_exists( 'rocket_notice_html' ) ) {
			require_once WP_ROCKET_ADMIN_UI_PATH . 'notices.php';
		}

		$this->original_user_id = get_current_user_id();
		set_current_screen( 'settings_page_wprocket' );

		$container        = apply_filters( 'rocket_container', null );
		$this->subscriber = $container->get( 'rocketcdn_notices_subscriber' );
	}

	/**
	 * Cleans up after each test.
	 *
	 * @return void
	 */
	public function tear_down() {
		wp_set_current_user( $this->original_user_id );
		delete_transient( 'rocket_cdn_pro_detection_failed' );

		parent::tear_down();
	}

	/**
	 * Creates an administrator with the WP Rocket capability and sets it as current.
	 *
	 * The capability is granted explicitly: the test suite's administrator role
	 * does not carry rocket_manage_options on its own.
	 *
	 * @return void
	 */
	private function set_admin_user(): void {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );
		wp_set_current_user( $user_id );
		wp_get_current_user()->add_cap( 'rocket_manage_options' );
	}

	/**
	 * Captures the notice output by calling the method directly.
	 *
	 * @return string
	 */
	private function get_actual_notice(): string {
		ob_start();
		$this->subscriber->display_pro_detection_failure_notice();
		return ob_get_clean();
	}

	/**
	 * Tests that no notice is displayed when the failure transient is absent.
	 */
	public function testShouldNotDisplayNoticeWhenNoTransient(): void {
		$this->set_admin_user();

		$this->assertSame( '', $this->get_actual_notice() );
	}

	/**
	 * Tests that no notice is displayed when the current user lacks the capability.
	 */
	public function testShouldNotDisplayNoticeWhenNoPermissions(): void {
		$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );
		wp_set_current_user( $user_id );

		set_transient( 'rocket_cdn_pro_detection_failed', true, MINUTE_IN_SECONDS );

		$this->assertSame( '', $this->get_actual_notice() );
	}

	/**
	 * Tests that the notice shows the copy and both actions when detection failed.
	 */
	public function testShouldDisplayNoticeWithCopyAndSupportLinkWhenDetectionFailed(): void {
		$this->set_admin_user();

		set_transient( 'rocket_cdn_pro_detection_failed', true, MINUTE_IN_SECONDS );

		$notice = $this->get_actual_notice();

		$this->assertStringContainsString( 'RocketCDN subscription check failed', $notice );
		$this->assertStringContainsString( 'We couldn’t confirm your RocketCDN subscription status. Refresh your customer data or contact support if the problem continues.', $notice );
		$this->assertStringContainsString( 'Refresh customer data', $notice );
		$this->assertStringContainsString( 'Contact support', $notice );
		$this->assertStringContainsString( 'wpr-rocketcdn-retry-pro-detection', $notice );
		$this->assertStringContainsString( 'wpr-rocketcdn-pro-detection-support', $notice );
	}
}
