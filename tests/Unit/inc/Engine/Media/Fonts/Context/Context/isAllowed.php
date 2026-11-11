<?php
declare(strict_types=1);

namespace WP_Rocket\Tests\Unit\inc\Engine\Media\Fonts\Context\Context;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Media\Fonts\Context\OptimizationContext;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @group HostFontsLocally
 */
class TestIsAllowed extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldDoExpected( $config, $expected ) {
		$this->donotrocketoptimize = $config['do_not_optimize'];

		$options = Mockery::mock( Options_Data::class );
		$context = new OptimizationContext( $options );

		Functions\when( 'rocket_bypass' )->justReturn( $config['bypass'] );

		$options->shouldReceive( 'get' )
			->with( 'host_fonts_locally', 0 )
			->andReturn( $config['option'] );

		$this->assertSame(
			$expected,
			$context->is_allowed( $config )
		);
	}
}
