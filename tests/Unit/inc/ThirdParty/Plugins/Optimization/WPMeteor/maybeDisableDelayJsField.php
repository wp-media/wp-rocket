<?php

namespace WP_Rocket\Tests\Unit\Inc\ThirdParty\Plugins\Optimization\WPMeteor;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\ThirdParty\Plugins\Optimization\WPMeteor::maybe_disable_delay_js_field
 *
 * @group WPMeteor
 * @group ThirdParty
 */
class Test_MaybeDisableDelayJsField extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $field, $expected ) {
		$this->stubTranslationFunctions();

		$meteor = new WPMeteor( Mockery::mock( Options::class), Mockery::mock( Options_Data::class ) );

		Functions\when( 'is_plugin_active' )->justReturn( $config['plugin_active'] );

		$this->assertSame(
			$expected,
			$meteor->maybe_disable_delay_js_field( $field )
		);
	}
}
