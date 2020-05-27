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
 * @covers \WP_Rocket\Engine\CriticalPath\Admin\Settings::add_hidden_async_css_mobile
 *
 * @group  CriticalPath
 * @group  CriticalPathSettings
 */
class Test_AddHiddenAsyncCssMobile extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldAddValueToArray( $hidden_fields, $expected ) {
		$settings = new Settings(
			Mockery::mock( Options_Data::class ),
			Mockery::mock( Beacon::class ),
			Mockery::mock( CriticalCSS::class ),
			'wp-content/plugins/wp-rocket/views/cpcss'
		);

        $this->assertSame(
			$expected,
			$settings->add_hidden_async_css_mobile( $hidden_fields )
		);
	}
}
