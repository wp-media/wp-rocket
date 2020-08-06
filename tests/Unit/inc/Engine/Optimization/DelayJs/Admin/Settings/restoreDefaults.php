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

		$actual = $settings->restore_defaults();

		if ( $restored ){
			$this->assertSame('', $actual);
		}else{
			$this->assertFalse( $actual );
		}

	}

}
