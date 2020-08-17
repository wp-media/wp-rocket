<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Subscriber;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::restore_defaults
 *
 * @group  DelayJS
 */
class Test_RestoreDefaults extends TestCase{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $result, $success ) {
		$settings   = Mockery::mock( Settings::class );
		$subscriber = new Subscriber( $settings, '' );

		Functions\when( 'check_ajax_referer' )->justReturn( true );

		$settings->shouldReceive( 'restore_defaults' )
			->once()
			->andReturn( $result );

		if ( $success ) {
			Functions\expect( 'wp_send_json_success' )
				->with( $result )
				->once();
			Functions\expect( 'wp_send_json_error' )
				->never();
		}else{
			Functions\expect( 'wp_send_json_error' )
				->once();
			Functions\expect( 'wp_send_json_success' )
				->never();
		}

		$subscriber->restore_defaults();
	}
}
