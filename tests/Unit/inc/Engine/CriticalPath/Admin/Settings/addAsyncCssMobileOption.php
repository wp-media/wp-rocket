<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\CriticalPath\Admin\Settings;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\CriticalPath\CriticalCSS;
use WP_Rocket\Engine\CriticalPath\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Settings::add_async_css_mobile_option
 *
 * @group  CriticalPath
 * @group  CriticalPathSettings
 */
class Test_AddAsyncCssMobileOption extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddOption( $options, $expected ) {
		$settings = new Settings(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Beacon::class ),
			Mockery::mock( CriticalCSS::class ),
			'wp-content/plugins/wp-rocket/views/metabox/cpcss'
		);

		$this->assertSame(
			$expected,
			$settings->add_async_css_mobile_option( $options )
		);
	}
}
