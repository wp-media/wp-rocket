<?php

namespace WP_Rocket\Tests\Unit\inc\Engine\Optimization\DeferJS\DeferJS;

use Brain\Monkey\Functions;
use Mockery;
use WP_Rocket\Admin\Options_Data;
use WP_Rocket\Engine\Optimization\DeferJS\DeferJS;
use WP_Rocket\Engine\Optimization\DynamicLists\DefaultLists\DataManager;
use WP_Rocket\Tests\Unit\TestCase;

/**
 * Test class covering \WP_Rocket\Engine\Optimization\DeferJS\DeferJS::defer_js
 *
 * @group  DeferJS
 */
class Test_DeferJs extends TestCase {
	/**
	 * @dataProvider configTestData
	 */
	public function testShouldReturnExpected( $config, $html, $expected ) {
		$this->donotrocketoptimize = $config['donotrocketoptimize'];
		$data_manager = Mockery::mock( DataManager::class );

		$data_manager->shouldReceive( 'get_lists' )
				->atMost()
				->once()
				->andReturn( $config['exclusions_list'] );

		$options  = Mockery::mock( Options_Data::class );
		$defer_js = new DeferJS( $options, $data_manager );

		$options->shouldReceive( 'get' )
			->atMost()
			->twice()
			->with( 'defer_all_js', 0 )
			->andReturn( $config['options']['defer_all_js'] );

		$options->shouldReceive( 'get' )
			->atMost()
			->once()
			->with( 'exclude_defer_js', [] )
			->andReturn( $config['options']['exclude_defer_js'] );

		Functions\when( 'is_rocket_post_excluded_option' )
			->justReturn( $config['post_meta'] );

		$this->assertSame(
			$expected,
			$defer_js->defer_js( $html )
		);
	}
}
