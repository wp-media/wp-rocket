<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Admin\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Addon\Cloudflare\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Admin\Subscriber::maybe_print_update_settings_notice
 *
 * @group Cloudflare
 */
class TestMaybePrintUpdateSettingsNotice extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'get_current_screen' )
			->justReturn( $config['current_screen'] );

		Functions\when( 'current_user_can' )
			->justReturn( $config['cap'] );

		Functions\when( 'get_current_user_id' )
			->justReturn( 1 );

		Functions\when( 'get_transient' )
			->justReturn( $config['transient'] );

		Functions\when( 'delete_transient' )
			->justReturn();

		$subscriber = new Subscriber();

		if ( null !== $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->once();
		} else {
			Functions\expect( 'rocket_notice_html' )
				->never();
		}

		$subscriber->maybe_display_update_settings_notice();
	}
}
