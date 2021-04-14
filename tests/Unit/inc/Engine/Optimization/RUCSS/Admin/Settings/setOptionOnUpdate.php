<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::set_option_on_update
 *
 * @group  RUCSS
 */
class Test_SetOptionOnUpdate extends TestCase{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $old_version, $valid_version ) {
		$options_data = Mockery::mock( Options_Data::class );
		$settings     = new Settings( $options_data );
		$options      = [
			'remove_unused_css'         => 0,
			'remove_unused_css_safelist' => [],
		];

		if ( $valid_version ) {
			Functions\when( 'get_option' )->justReturn( [] );
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $options )
				->once();
		} else {
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $options )
				->never();
		}

		$settings->set_option_on_update( $old_version );

	}

}
