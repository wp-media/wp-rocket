<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::set_option_on_update
 *
 * @group  DelayJS
 */
class Test_SetOptionOnUpdate extends TestCase{

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $old_version, $valid_version ) {
		$options_data = Mockery::mock( Options_Data::class );
		$settings     = new Settings( $options_data );
		$options      = [ 'delay_js' => 0 ];

		if ( $valid_version ) {
			$options_data->shouldReceive( 'set' )
				->with( 'delay_js', 0 )
				->once();
			$options_data->shouldReceive( 'get_options' )
				->once()
				->andReturn( $options );
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $options )
				->once();
		} else {
			$options_data->shouldReceive( 'set' )
				->with( 'delay_js', 0 )
				->never();
			$options_data->shouldReceive( 'get_options' )
				->never();
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $options )
				->never();
		}

		$settings->set_option_on_update( $old_version );

	}

}
