<?php

namespace WP_Rocket\Tests\Unit\Inc\Addon\Cloudflare\Admin\Subscriber;

use Brain\Monkey\Functions;
use WP_Rocket\Addon\Cloudflare\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers WP_Rocket\Addon\Cloudflare\Admin\Subscriber::maybe_display_purge_notice
 *
 * @group Cloudflare
 */
class TestMaybeDisplayPurgeNotice extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'current_user_can' )
			->justReturn( $config['cap'] );

		Functions\when( 'get_current_user_id' )
			->justReturn( 1 );

		Functions\when( 'get_transient' )
			->justReturn( $config['transient'] );

		Functions\when( 'delete_transient' )
			->justReturn();

		$subscriber = new Subscriber();

		if ( '' !== $expected ) {
			Functions\expect( 'rocket_notice_html' )
				->once();
		} else {
			Functions\expect( 'rocket_notice_html' )
				->never();
		}

		$subscriber->maybe_display_purge_notice();
	}
}
