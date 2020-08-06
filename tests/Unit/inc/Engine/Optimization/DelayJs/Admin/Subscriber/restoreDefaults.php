<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJs\Admin\Subscriber;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Subscriber::restore_defaults
 *
 * @group  DelayJs
 */
class Test_RestoreDefaults extends TestCase{

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $restored ){
		$capabilities = isset( $input['capabilities'] ) ? $input['capabilities'] : [] ;

		$options_data = Mockery::mock( Options_Data::class );
		$settings = new Settings( $options_data );
		$subscriber = new Subscriber($settings, '');

		Functions\when( 'check_ajax_referer' )->justReturn( true );

		if ( $capabilities ) {
			foreach ( $capabilities as $capability ){
				Functions\expect( 'current_user_can' )->with( $capability )->andReturn( true );
			}
		} else {
			Functions\when( 'current_user_can' )->justReturn( false );
		}

		if ( $restored ){
			Functions\expect( 'wp_send_json_success' )->with( '' )->once();
			Functions\expect( 'wp_send_json_error' )->never();
		}else{
			Functions\expect( 'wp_send_json_error' )->once();
			Functions\expect( 'wp_send_json_success' )->never();
		}

		$subscriber->restore_defaults();

	}

}
