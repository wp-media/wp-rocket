<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DeferJS\DeferJS;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Engine\Optimization\DynamicLists\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * @covers \WP_Rocket\Engine\Optimization\DeferJS\DeferJS::exclude_jquery_combine
 *
 * @group  DeferJS
 */
class Test_ExcludeJqueryCombine extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $excluded, $expected ) {
		$this->donotrocketoptimize = $config['donotrocketoptimize'];

		$options  = Mockery::mock( Options_Data::class );
		$defer_js = new DeferJS( $options, Mockery::mock( DataManager::class ) );

		$options->shouldReceive( 'get' )
			->atMost()
			->once()
			->with( 'defer_all_js', 0 )
			->andReturn( $config['options']['defer_all_js'] );

		Functions\when( 'is_rocket_post_excluded_option' )
			->justReturn( $config['post_meta'] );

		$options->shouldReceive( 'get' )
			->atMost()
			->once()
			->with( 'minify_concatenate_js', 0 )
			->andReturn( $config['options']['minify_concatenate_js'] );

		$this->assertSame(
			$expected,
			$defer_js->exclude_jquery_combine( $excluded )
		);
	}
}
