<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\RUCSS\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Admin\Beacon\Beacon;
use WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings;
use WP_Rocket\Engine\Optimization\RUCSS\Database\Tables\UsedCSS;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\RUCSS\Admin\Settings::add_options
 *
 * @group  RUCSS
 */
class Test_AddOptions extends TestCase{
	private $used_css;
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $input, $expected ){
		$options  = isset( $input['options'] )  ? $input['options']  : [];
		$this->used_css = $this->createMock(UsedCSS::class);
		$settings = new Settings( Mockery::mock( Options_Data::class ), Mockery::mock( Beacon::class ), $this->used_css );

		$this->assertSame(
			$expected,
			$settings->add_options( $options )
		);
	}
}
