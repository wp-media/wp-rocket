<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJs\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;
use Brain\Monkey\Functions;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::restore_defaults
 *
 * @group  DelayJs
 */
class Test_RestoreDefaults extends TestCase{

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $restored ){
		$capabilities = isset( $input['capabilities'] ) ? $input['capabilities'] : [] ;
		$options = isset( $input['options'] ) ? $input['options'] : [] ;

		$options_data = Mockery::mock( Options_Data::class );
		$settings = new Settings( $options_data );

		if ( $capabilities ) {
			foreach ( $capabilities as $capability ){
				Functions\expect( 'current_user_can' )->with( $capability )->andReturn( true );
			}
		} else {
			Functions\when( 'current_user_can' )->justReturn( false );
		}

		if ( $restored ) {
			foreach ( $options as $option => $option_value ) {
				$options_data->shouldReceive( 'set' )->with( $option, $option_value )->once();
			}

			$options_data->shouldReceive('get_options')->once()->andReturn( $options );
			Functions\expect( 'update_option' )->with( 'wp_rocket_settings', $options )->once();
		} else {
			$options_data->shouldReceive('get_options')->never();
			Functions\expect( 'update_option' )->with( 'wp_rocket_settings', $options )->never();
		}

		$settings->restore_defaults();

	}

}
