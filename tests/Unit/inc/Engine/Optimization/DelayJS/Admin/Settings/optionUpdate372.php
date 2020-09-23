<?php
namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::option_update_3_7_2
 *
 * @group  DelayJS
 */
class Test_OptionUpdate372 extends TestCase{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$options_data = Mockery::mock( Options_Data::class );
		$settings     = new Settings( $options_data );
		$options      = [
			'delay_js_scripts' => $config['initial_list'],
		];

		$revised_options = [
			'delay_js_scripts' => $expected
		];

		if ( $config['valid_version'] ) {
			Functions\when( 'get_option' )->justReturn( $options );
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $revised_options )
				->once();
		} else {
			Functions\expect( 'update_option' )
				->with( 'wp_rocket_settings', $revised_options )
				->never();
		}

		$settings->option_update_3_7_2( $config['old_version'] );
	}
}
