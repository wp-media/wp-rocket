<?php

namespace WP_Rocket\Tests\Unit\inc\admin\ui;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_analytics_optin_notice
 *
 * @covers ::rocket_analytics_optin_notice
 * @group  admin
 * @group  Notices
 * @runTestsInSeparateProcesses
 * @preserveGlobalState disabled
 */
class Test_RocketAnalyticsOptinNotice extends TestCase {

	/**
	 * Sets up the test fixture.
	 *
	 * @return void
	 */
	public function setUp(): void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/ui/notices.php';
	}

	/**
	 * Mocks all WordPress functions needed to reach rocket_notice_html.
	 *
	 * @return void
	 */
	private function setUpHappyPathMocks(): void {
		$screen     = new \stdClass();
		$screen->id = 'settings_page_wprocket';

		Functions\when( 'get_current_screen' )->justReturn( $screen );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_option' )->justReturn( false );
		Functions\when( '__' )->returnArg( 1 );
		Functions\when( 'esc_html__' )->returnArg( 1 );
		Functions\when( 'rocket_data_collection_preview_table' )->justReturn( '<table></table>' );
		Functions\when( 'admin_url' )->justReturn( 'http://example.com/wp-admin/admin-post.php' );
		Functions\when( 'wp_nonce_url' )->justReturn( 'http://example.com/wp-admin/admin-post.php?_wpnonce=abc123' );
	}

	/**
	 * Tests that rocket_notice_html is called with dismissible set to an empty string
	 * when all conditions are met.
	 *
	 * @return void
	 */
	public function testShouldCallNoticeHtmlWithEmptyDismissible(): void {
		$this->setUpHappyPathMocks();

		Functions\expect( 'rocket_notice_html' )
			->once()
			->with(
				Mockery::on(
					function ( $args ) {
						return isset( $args['dismissible'] ) && '' === $args['dismissible'];
					}
				)
			)
			->andReturnNull();

		rocket_analytics_optin_notice();
	}

	/**
	 * Tests that rocket_notice_html is never called when the user lacks the required capability.
	 *
	 * @return void
	 */
	public function testShouldNotDisplayWhenUserLacksCapability(): void {
		$screen     = new \stdClass();
		$screen->id = 'settings_page_wprocket';

		Functions\when( 'get_current_screen' )->justReturn( $screen );
		Functions\when( 'current_user_can' )->justReturn( false );

		Functions\expect( 'rocket_notice_html' )->never();

		rocket_analytics_optin_notice();
	}

	/**
	 * Tests that rocket_notice_html is never called when the current screen is not the WP Rocket settings page.
	 *
	 * @return void
	 */
	public function testShouldNotDisplayWhenNotOnRocketSettingsPage(): void {
		$screen     = new \stdClass();
		$screen->id = 'dashboard';

		Functions\when( 'get_current_screen' )->justReturn( $screen );
		Functions\when( 'current_user_can' )->justReturn( true );

		Functions\expect( 'rocket_notice_html' )->never();

		rocket_analytics_optin_notice();
	}

	/**
	 * Tests that rocket_notice_html is never called when the notice has already been displayed.
	 *
	 * @return void
	 */
	public function testShouldNotDisplayWhenNoticeAlreadyDisplayed(): void {
		$screen     = new \stdClass();
		$screen->id = 'settings_page_wprocket';

		Functions\when( 'get_current_screen' )->justReturn( $screen );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_option' )->alias(
			function ( $option ) {
				if ( 'rocket_analytics_notice_displayed' === $option ) {
					return 1;
				}
				return false;
			}
		);

		Functions\expect( 'rocket_notice_html' )->never();

		rocket_analytics_optin_notice();
	}

	/**
	 * Tests that rocket_notice_html is never called when the user has already opted in to analytics.
	 *
	 * @return void
	 */
	public function testShouldNotDisplayWhenUserAlreadyOptedIn(): void {
		$screen     = new \stdClass();
		$screen->id = 'settings_page_wprocket';

		Functions\when( 'get_current_screen' )->justReturn( $screen );
		Functions\when( 'current_user_can' )->justReturn( true );
		Functions\when( 'get_option' )->alias(
			function ( $option ) {
				if ( 'rocket_mixpanel_optin' === $option ) {
					return 'yes';
				}
				return false;
			}
		);

		Functions\expect( 'rocket_notice_html' )->never();

		rocket_analytics_optin_notice();
	}
}
