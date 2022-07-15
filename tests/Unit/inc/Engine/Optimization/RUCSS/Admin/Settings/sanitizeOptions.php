<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::sanitize_options
 *
 * @group  RUCSS
 */
class Test_SanitizeOptions extends TestCase{
	private $used_css;

	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'rocket_sanitize_textarea_field' )->justReturn( $config['sanitized_input'] );
		$this->used_css = Mockery::mock(UsedCSS::class);

		$options_data   = Mockery::mock( Options_Data::class );
		$settings       = new Settings( $options_data, Mockery::mock( Beacon::class ), $this->used_css );
		$admin_settings = new AdminSettings( $options_data );

		$this->assertSame(
			$expected,
			$settings->sanitize_options( $config['input'], $admin_settings )
		);
	}
}
