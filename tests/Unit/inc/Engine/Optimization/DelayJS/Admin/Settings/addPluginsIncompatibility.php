<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::add_plugins_incompatibility
 *
 * @group  DelayJS
 */
class Test_AddPluginsIncompatibility extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $options_data, $plugins, $expected ) {
		$options  = Mockery::mock( Options_Data::class );
		$settings = new Settings( $options );

		$options->shouldReceive( 'get' )
			->once()
			->with( 'delay_js', 0 )
			->andReturn( $options_data['delay_js'] );

		$this->assertSame(
			$expected,
			$settings->add_plugins_incompatibility( $plugins )
		);
	}
}
