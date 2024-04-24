<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::update_safelist_items
 *
 * @group  RUCSS
 */
class Test_UpdateSafelistItems extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$settings = new Settings( Mockery::mock( Options_Data::class ), Mockery::mock( Beacon::class ), $this->createMock(UsedCSS::class) );

		Functions\when( 'get_option' )->justReturn( $config['options'] );

		if ( $expected ) {
			Functions\expect( 'update_option' )
				->once()
				->with( 'wp_rocket_settings', $expected );
		} else {
			Functions\expect( 'update_option' )->never();
		}


		$settings->update_safelist_items( $config['version'] );

	}
}
