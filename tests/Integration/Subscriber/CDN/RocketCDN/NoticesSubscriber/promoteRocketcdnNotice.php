<?php

namespace WP_Rocket\Tests\Integration\Subscriber\CDN\RocketCDN;

use WPMedia\PHPUnit\Integration\TestCase;

/**
 * @covers \WP_Rocket\Subscriber\CDN\RocketCDN\NoticesSubscriber::promote_rocketcdn_notice
 * @group  RocketCDN
 * @group  AdminOnly
 */
class Test_PromoteRocketcdnNotice extends TestCase {
	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}

	private function get_notice() {
		return $this->format_the_html( '<div class="notice notice-alt notice-warning is-dismissible" id="rocketcdn-promote-notice">
		<h2 class="notice-title">New!</h2>
		<p>Speed up your website with Rocket CDN, WP Rocket’s Content Delivery Network!</p>
		<p><a href="#page_cdn" class="wpr-button" id="rocketcdn-learn-more-dismiss">Learn More</a></p>
	</div>' );
	}

	/**
	 * Test should not display the notice when current user doesn't have the capability
	 */
	public function testShouldNotDisplayNoticeWhenNoCapability() {
		$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );

		wp_set_current_user( $user_id );

		$this->assertNotContains( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should not display the notice when not on WP Rocket settings page
	 */
	public function testShouldNotDisplayNoticeWhenNotRocketPage() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );
		set_current_screen( 'edit.php' );

		$this->assertNotContains( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should not display the notice when the notice has been dismissed
	 */
	public function testShouldNotDisplayNoticeWhenDismissed() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );
		add_user_meta( get_current_user_id(), 'rocketcdn_dismiss_notice', true );

		$this->assertNotContains( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should not display the notice when RocketCDN is active
	 */
	public function testShouldNotDisplayNoticeWhenActive() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'running' ], MINUTE_IN_SECONDS );

		$this->assertNotContains( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should display the notice when RocketCDN is inactive
	 */
	public function testShouldDisplayNoticeWhenNotActive() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );
		set_transient( 'rocketcdn_status', [ 'subscription_status' => 'cancelled' ], MINUTE_IN_SECONDS );

		$this->assertContains( $this->get_notice(), $this->getActualHtml() );
	}
}
