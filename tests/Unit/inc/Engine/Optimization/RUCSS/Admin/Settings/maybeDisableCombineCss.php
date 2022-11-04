<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::maybe_disable_combine_css
 *
 * @group  RUCSS
 */
class Test_MaybeDisableCombineCss extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$settings = new Settings( Mockery::mock( Options_Data::class ), Mockery::mock( Beacon::class ), $this->createMock(UsedCSS::class) );

		$this->assertSame(
			$expected,
			$settings->maybe_disable_combine_css( $config['value'], $config['old_value'] )
		);
	}
}
