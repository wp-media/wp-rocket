<?php

namespace WP_Rocket\Tests\Unit\inc\admin\ui\notices;

use Brain\Monkey\Functions;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering ::rocket_analytics_optin_thankyou_notice
 * 
 * @group admin
 * @group notices
 * @runTestsInSeparateProcesses
 */
class Test_RocketAnalyticsOptinThankyouNotice extends TestCase {

	protected function setUp(): void {
		parent::setUp();
		require_once WP_ROCKET_PLUGIN_ROOT . 'inc/admin/ui/notices.php';
	}

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldRenderNotice( $config, $expected ): void {
		$screen     = \Mockery::mock( \WP_Screen::class );
		$screen->id = $config['screen_id'];

		Functions\expect( 'get_current_screen' )
			->once()
			->andReturn( $screen );

		Functions\expect( 'current_user_can' )
			->once()
			->with( 'rocket_manage_options' )
			->andReturn( $config['user_can'] );

		if ( $expected['transient_checked'] ) {
			Functions\expect( 'get_transient' )
				->once()
				->with( 'rocket_analytics_optin' )
				->andReturn( $config['transient_value'] );
		} else {
			Functions\expect( 'get_transient' )->never();
		}

		Functions\when( '__' )->returnArg( 1 );
		Functions\expect( 'rocket_data_collection_preview_table' )->never();

		if ( $expected['notice_rendered'] ) {
			Functions\expect( 'rocket_notice_html' )
				->once()
				->andReturnUsing(
					function ( $args ) {
						$this->assertArrayHasKey( 'message', $args );
						$this->assertStringContainsString( 'Thank you! The data you share helps us improve WP Rocket', $args['message'] );
					}
				);
		} else {
			Functions\expect( 'rocket_notice_html' )->never();
		}

		if ( $expected['transient_deleted'] ) {
			Functions\expect( 'delete_transient' )
				->once()
				->with( 'rocket_analytics_optin' );
		} else {
			Functions\expect( 'delete_transient' )->never();
		}

		rocket_analytics_optin_thankyou_notice();
	}
}
