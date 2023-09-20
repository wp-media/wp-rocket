<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Admin\Settings\Settings as AdminSettings;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::sanitize_options
 *
 * @group  DelayJS
 */
class Test_SanitizeOptions extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		Functions\when( 'rocket_sanitize_textarea_field' )->justReturn( $config['sanitized_input']['delay_js_exclusions'] );

		$admin_settings = Mockery::mock( AdminSettings::class );
		$settings = new Settings( Mockery::mock(Options::class)
		);

		$admin_settings->shouldReceive( 'sanitize_checkbox' )
			->atMost()
			->once()
			->with( $config['input'], 'delay_js' )
			->andReturn( $config['sanitized_input']['delay_js'] );

		$this->assertSame(
			$expected,
			$settings->sanitize_options( $config['input'], $admin_settings )
		);
	}
}
