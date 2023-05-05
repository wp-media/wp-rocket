<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DelayJS\Admin\Settings;

use Mockery;
use WP_Rocket\Admin\Options;
use WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DelayJS\Admin\Settings::maybe_disable_combine_js
 *
 * @group  DelayJS
 */
class Test_MaybeDisableCombineJs extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$settings = new Settings( Mockery::mock(Options::class));

		$this->assertSame(
			$expected,
			$settings->maybe_disable_combine_js( $config['value'], $config['old_value'] )
		);
	}
}
