<?php

namespace WP_Rocket\Tests\Integration\inc\Engine\CDN\RocketCDN\NoticesSubscriber;

use WPMedia\PHPUnit\Integration\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\CDN\RocketCDN\NoticesSubscriber::purge_cache_notice
 * @uses ::rocket_notice_html
 *
 * @group  AdminOnly
 * @group  RocketCDN
 */
class Test_PurgeCacheNotice extends TestCase {
	public static function set_up_before_class() {
		$role = get_role( 'administrator' );
		$role->add_cap( 'rocket_manage_options' );
	}

	private function get_notice( $status = 'success', $message = '' ) {
		return $this->format_the_html( '<div class="notice notice-' . $status . ' is-dismissible">
		<p>' . $message . '</p>
		</div>' );
	}

	private function getActualHtml() {
		ob_start();
		do_action( 'admin_notices' );

		return $this->format_the_html( ob_get_clean() );
	}

	/**
	 * Test should not display notice when current user doesn't have capability
	 */
	public function testShouldNotDisplayNoticeWhenNoPermissions() {
		$user_id = self::factory()->user->create( [ 'role' => 'editor' ] );

		wp_set_current_user( $user_id );
		set_current_screen( 'edit.php' );

		$this->assertStringNotContainsString( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should not display notice when not on WP Rocket settings page
	 */
	public function testShouldNotDisplayNoticeWhenNotRocketPage() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );
		set_current_screen( 'edit.php' );

		$this->assertStringNotContainsString( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should not display notice when there is no transient value
	 */
	public function testShouldNotDisplayNoticeWhenNoTransient() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );

		$this->assertStringNotContainsString( $this->get_notice(), $this->getActualHtml() );
	}

	/**
	 * Test should display notice when the transient value is set
	 */
	public function testShouldDisplayNoticeWhenTransient() {
		$user_id = self::factory()->user->create( [ 'role' => 'administrator' ] );

		wp_set_current_user( $user_id );

		set_current_screen( 'settings_page_wprocket' );
		set_transient( 'rocketcdn_purge_cache_response', [ 'status' => 'success', 'message' => 'RocketCDN cache purge successful.' ], MINUTE_IN_SECONDS );

		Functions\expect('delete_transient')
		->once()
		->with( 'rocketcdn_purge_cache_response' );

		$this->assertStringContainsString( $this->get_notice( 'success', 'RocketCDN cache purge successful.' ), $this->getActualHtml() );
	}
}
