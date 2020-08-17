<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::restore_defaults
 *
 * @group  DelayJS
 */
class Test_RestoreDefaults extends TestCase{
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $capability, $restored ) {
		$settings = new Settings( Mockery::mock( Options_Data::class ) );

		Functions\expect( 'current_user_can' )
			->with( 'rocket_manage_options' )
			->andReturn( $capability );

		$actual = $settings->restore_defaults();

		if ( false !== $restored ) {
			$this->assertSame( $restored, $actual );
		} else {
			$this->assertFalse( $actual );
		}
	}
}
