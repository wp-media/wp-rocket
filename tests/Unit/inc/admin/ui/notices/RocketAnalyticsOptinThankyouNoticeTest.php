<?php

namespace WP_Rocket\Tests\Unit\inc\admin\ui\notices;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_analytics_optin_thankyou_notice
 *
 * @group admin
 * @group notices
 * @group analytics
 */
class RocketAnalyticsOptinThankyouNoticeTest extends TestCase {

	/**
	 * Set up the test environment.
	 */
	protected function setUp(): void {
		parent::setUp();

		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/ui/notices.php';
	}

	/**
	 * Test that when the transient exists and conditions are met, the thank-you notice is
	 * rendered without the data collection table, and the transient is deleted.
	 */
	public function testShouldRenderNoticeWithoutDataTableWhenTransientExistsAndConditionsMet(): void {
		$screen     = \Mockery::mock( \WP_Screen::class );
		$screen->id = 'settings_page_wprocket';

		Functions\expect( 'get_current_screen' )
			->once()
			->andReturn( $screen );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( true );

		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_analytics_optin' )
			->andReturn( 1 );

		Functions\when( '__' )->returnArg( 1 );

		Functions\expect( 'rocket_notice_html' )
			->once()
			->andReturnUsing(
				function ( $args ) {
					$this->assertArrayHasKey( 'message', $args );
					$this->assertStringContainsString( 'Thank you!', $args['message'] );
					$this->assertStringNotContainsString( 'WP Rocket now collects these metrics', $args['message'] );
				}
			);

		Functions\expect( 'rocket_data_collection_preview_table' )
			->never();

		Functions\expect( 'delete_transient' )
			->once()
			->with( 'rocket_analytics_optin' );

		rocket_analytics_optin_thankyou_notice();
	}

	/**
	 * Test that when the transient is absent, nothing is rendered.
	 */
	public function testShouldRenderNothingWhenTransientIsAbsent(): void {
		$screen     = \Mockery::mock( \WP_Screen::class );
		$screen->id = 'settings_page_wprocket';

		Functions\expect( 'get_current_screen' )
			->once()
			->andReturn( $screen );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( true );

		Functions\expect( 'get_transient' )
			->once()
			->with( 'rocket_analytics_optin' )
			->andReturn( false );

		Functions\expect( 'rocket_notice_html' )
			->never();

		Functions\expect( 'delete_transient' )
			->never();

		rocket_analytics_optin_thankyou_notice();
	}

	/**
	 * Test that when the screen is not the WP Rocket settings page, nothing is rendered.
	 */
	public function testShouldRenderNothingWhenScreenIsWrong(): void {
		$screen     = \Mockery::mock( \WP_Screen::class );
		$screen->id = 'dashboard';

		Functions\expect( 'get_current_screen' )
			->once()
			->andReturn( $screen );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( true );

		Functions\expect( 'rocket_notice_html' )
			->never();

		Functions\expect( 'get_transient' )
			->never();

		Functions\expect( 'delete_transient' )
			->never();

		rocket_analytics_optin_thankyou_notice();
	}
}
